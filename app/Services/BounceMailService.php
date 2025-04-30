<?php

namespace App\Services;

use App\Models\UserBouncesInfo;
use App\Models\Admin\Site\SystemSetting\BouncePattern;
use App\Models\EmailList;
use App\Models\JobProgress;
use App\Models\User\Reports\EmailBounce;
use App\Traits\TracksProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BounceMailService
{
    use TracksProgress;

    private $mailbox;
    private $connection;
    private $user;
    private $jobId;

    protected $patterns = [
        'subject' => [],
        'hard' => [],
        'soft' => []
    ];

    public function __construct( $bounceInfo = null, $jobId = null)
    {
        $this->user = $bounceInfo;
        $this->jobId = $jobId;

        if ($this->user) {
            $this->loadBouncePatterns();
        }
    }


    // Connection

    public function connect(): bool
    {
        if (!extension_loaded('imap')) {
            throw new \Exception('PHP IMAP extension is not installed or enabled. Please enable it in your php.ini');
        }

        $mailbox = '{'.$this->user->mail_server.':'.$this->user->imap_port.'/imap/ssl/novalidate-cert/notls}INBOX';

        $this->connection = @\imap_open($mailbox, $this->user->bounce_inbox, $this->user->bounce_inbox_password);

        if (!$this->connection) {
            throw new \Exception('Cannot connect to IMAP: ' . imap_last_error());
        }

        $this->mailbox = $mailbox;
        return true;
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            imap_close($this->connection);
            $this->connection = null;
        }
    }

    public function testConnection(): bool
    {
        try {
            $this->connect();
            $status = imap_status($this->connection, $this->mailbox, SA_ALL);
            $this->disconnect();

            return $status ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }








    // Main Code

    // Load All Bounce Patterns
    protected function loadBouncePatterns(): void
    {
        foreach (['subject', 'hard', 'soft'] as $type) {
            $this->patterns[$type] = BouncePattern::getPatterns($type);
        }
    }




    public function markUnreadMessages(): void
    {
        // Initialize progress tracking with initial estimate of 10
        // We'll update this with actual counts once we know them
        $this->initializeProgress('process_bounce_emails', $this->user->user_id, 10);

        try {
            $messages = $this->getAllUnreadMessages();
            $totalProcessed = count($messages);

            // Make sure the total is at least 1 to avoid division by zero
            $this->updateProgressTotal($totalProcessed > 0 ? $totalProcessed : 1);

            if ($totalProcessed > 0) {
                $bounceRecords = [];

                foreach ($messages as $index => $message) {
                    if (!empty($message['affected_email'])) {
                        // Prepare data for email_bounces table
                        $bounceRecords[] = [
                            'user_id' => $this->user->user_id,
                            'email' => $message['affected_email'],
                            'type' => $message['bounce_type'],
                            'created_at' => now()
                        ];
                    }

                    // Update progress for each categorized message
                    $this->updateProgress($index + 1);
                }

                // Bulk insert into email_bounces table
                if (!empty($bounceRecords)) {
                    EmailBounce::insert($bounceRecords);
                    Log::channel('emailBounces')->info('Added ' . count($bounceRecords) . ' email bounce records');
                }
            }

            $this->completeProgress();

        } catch (\Exception $e) {
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }


    public function getAllUnreadMessages(): array {
        // Initialize tracking with an estimate
        $messages = [];
        $processedCount = 0;
        $matchingCount = 0;
        $totalEmailsToProcess = 0;

        // Get all subject patterns
        $subjectPatterns = array_map('trim', $this->patterns['subject']);

        // Keep track of which message numbers we've already seen
        $processedMessageNumbers = [];

        // Process each subject pattern separately
        foreach ($subjectPatterns as $pattern) {

            Log::channel('emailBounces')->info("Searching for subject pattern: '$pattern'");
            // Use IMAP's built-in SUBJECT search criteria
            $searchCriteria = 'UNSEEN SUBJECT "' . $pattern . '"';
            $matchingEmails = imap_search($this->connection, $searchCriteria);

            // If no emails match this pattern, continue to the next pattern
            if (!$matchingEmails) {
                continue;
            }

            // Count only new emails we haven't seen before
            $newEmails = array_diff($matchingEmails, $processedMessageNumbers);
            $newEmailCount = count($newEmails);

            // Add these message numbers to our processed list
            $processedMessageNumbers = array_merge($processedMessageNumbers, $matchingEmails);

            // Add to total count
            $totalEmailsToProcess += $newEmailCount;

            // Update progress total with the additional emails
            if ($this->jobProgress && $newEmailCount > 0) {
                $this->updateProgressTotal($totalEmailsToProcess);
            }

            Log::channel('emailBounces')->info("Found " . count($matchingEmails) . " unread messages with subject pattern: '$pattern'");

            // Process the matching emails
            $headers = imap_fetch_overview($this->connection, implode(',', $matchingEmails), 0);

            foreach ($headers as $header) {
                $emailNumber = $header->msgno;
                $subject = isset($header->subject) ? imap_utf8($header->subject) : '';

                try {
                    // Get the properly decoded message body
                    $rawBody = imap_body($this->connection, $emailNumber, FT_PEEK);


                    $bounceType = $this->analyzeBounceMessage($rawBody);
                    $affectedEmail = $this->extractAffectedEmail($rawBody);

                    Log::channel('emailBounces')->info("---------------------------------------------------------------------");
                    Log::channel('emailBounces')->debug("bounceType {$bounceType}  affectedEmail {$affectedEmail}");
                    Log::channel('emailBounces')->info("---------------------------------------------------------------------");

                    if (!empty($affectedEmail) && $bounceType) {
                        $messages[] = [
                            'number' => $emailNumber,
                            'subject' => $subject,
                            'bounce_type' => $bounceType,
                            'affected_email' => $affectedEmail,
                        ];
                        $matchingCount++;
                        // Only mark as read if processing was successful
                        imap_setflag_full($this->connection, $emailNumber, '\\Seen');
                    }

                    $processedCount++;

                    // Update progress after each item is processed
                    if ($this->jobProgress) {
                        $this->updateProgress($processedCount);
                    }

                } catch (\Exception $e) {
                    Log::channel('emailBounces')->error("Error processing email {$emailNumber}: " . $e->getMessage());
                    $processedCount++;

                    // Update progress even when errors occur
                    if ($this->jobProgress) {
                        $this->updateProgress($processedCount);
                    }
                }
            }
        }

        Log::channel('emailBounces')->info("Total processed: $processedCount, matching: $matchingCount");
        return $messages;
    }



    protected function patternMatch(string $text, array $patterns): bool
    {
        $text = trim(strtolower($text));

        foreach ($patterns as $pattern) {
            $pattern = trim(strtolower($pattern));

            // Skip empty patterns
            if (empty($pattern)) {
                continue;
            }

            // More robust matching that handles word boundaries
            if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/i', $text)) {
                Log::channel('emailBounces')->info("---------------------------------------------------------------------");
                Log::channel('emailBounces')->info("Matched pattern: '{$pattern}' in {$text}");
                Log::channel('emailBounces')->info("---------------------------------------------------------------------");
                return true;
            }
        }

        Log::channel('emailBounces')->info("---------------------------------------------------------------------");
        Log::channel('emailBounces')->debug("No pattern matches found in text {$text}");
        Log::channel('emailBounces')->info("---------------------------------------------------------------------");

        return false;
    }






    protected function extractAffectedEmail(string $body): ?string
    {
        // First try to find email in final-recipient field
        if (preg_match('/final-recipient:\s*rfc822;\s*([^\s\n]+@[^\s\n]+)/i', $body, $matches)) {
            return trim($matches[1]);
        }
        // If not found, try original-recipient field
        elseif (preg_match('/original-recipient:\s*rfc822;\s*([^\s\n]+@[^\s\n]+)/i', $body, $matches)) {
            return trim($matches[1]);
        }
        // Finally, try to find any email address in the body
        elseif (preg_match('/\b([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/', $body, $matches)) {
            return $matches[0];
        }

        return null;
    }


    public function analyzeBounceMessage(string $data): ?string
    {
        if ($this->patternMatch($data, $this->patterns['hard'])) {
            return 'hard';
        }

        if ($this->patternMatch($data, $this->patterns['soft'])) {
            return 'soft';
        }

        return null;
    }



    public function getFirstUnreadMessageForTest(): void
    {
        // Search for all unread (UNSEEN) emails
        $unreadEmails = imap_search($this->connection, 'UNSEEN');

        if (!$unreadEmails) {
            Log::channel('emailBounces')->info("No unread emails found.");
            return;
        }

        // Get the first unread email only
        $firstEmailNumber = $unreadEmails[0];

        try {
            // Fetch subject (for debug/logging)
            $overview = imap_fetch_overview($this->connection, $firstEmailNumber, 0);
            $subject = isset($overview[0]->subject) ? imap_utf8($overview[0]->subject) : '(No subject)';
            Log::channel('emailBounces')->info("First unread email subject: $subject");

            // Fetch and decode the body using your existing method
            $text = imap_body($this->connection, $firstEmailNumber);


            // Print body
            Log::channel('emailBounces')->info("text sss: $text");


            // Optionally mark as seen (so it doesn't show again)
            imap_setflag_full($this->connection, $firstEmailNumber, '\\Seen');

        } catch (\Exception $e) {
            Log::channel('emailBounces')->error("Error fetching first unread email: " . $e->getMessage());
        }
    }









    /**
     * Apply bounce records to the main email list table
     *
     * @param int $userId Optional user ID to limit which records to process
     * @return array Stats about processed records
     */
    public function applyBouncesToEmailList($userId): array
    {
        $stats = [
            'hard_bounces' => 0,
            'soft_bounces' => 0,
            'converted_to_hard' => 0
        ];

        try {
            DB::beginTransaction();

            // Get all hard bounces
            $hardBounceEmails = EmailBounce::where('type', 'hard')
                ->when($userId, function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->pluck('email')
                ->toArray();


            // Get all soft bounces
            $softBounceEmails = EmailBounce::where('type', 'soft')
                ->when($userId, function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->pluck('email')
                ->toArray();


            // Process hard bounces
            if (!empty($hardBounceEmails)) {
                $affected = EmailList::when($userId, function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->whereIn('email', $hardBounceEmails)
                    ->update(['is_hard_bounce' => true]);

                $stats['hard_bounces'] = $affected;
                Log::channel('emailBounces')->info("Applied $affected hard bounces to email list");
            }


            // Process soft bounces
            if (!empty($softBounceEmails)) {
                // Count how many times each soft bounce email occurred
                $emailCounts = array_count_values($softBounceEmails);

                // Get current email list records for these emails
                $emailsToCheck = EmailList::when($userId, function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->whereIn('email', array_keys($emailCounts))
                    ->select('id', 'email', 'user_id', 'soft_bounce_counter')
                    ->get();

                $affectedSoft = 0;
                $hardBounceIds = [];

                foreach ($emailsToCheck as $email) {
                    $incrementBy = $emailCounts[$email->email] ?? 0;

                    if ($incrementBy > 0) {
                        // Increment the counter
                        EmailList::where('id', $email->id)->increment('soft_bounce_counter', $incrementBy);
                        $affectedSoft++;

                        // Get max allowed soft bounces
                        $userBounceInfo = UserBouncesInfo::where('user_id', $email->user_id)->first();
                        $maxSoftBounces = $userBounceInfo ? $userBounceInfo->max_soft_bounces : 5;

                        // Check if it should now be marked as a hard bounce
                        if (($email->soft_bounce_counter + $incrementBy) >= $maxSoftBounces) {
                            $hardBounceIds[] = $email->id;
                        }
                    }
                }

                $stats['soft_bounces'] = $affectedSoft;

                // Convert to hard bounces if necessary
                if (!empty($hardBounceIds)) {
                    $convertedCount = EmailList::whereIn('id', $hardBounceIds)
                        ->update(['is_hard_bounce' => true]);

                    $stats['converted_to_hard'] = $convertedCount;
                    Log::channel('emailBounces')->info("Converted $convertedCount soft bounces to hard bounces");
                }
            }


            DB::commit();
            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('emailBounces')->error("Error applying bounces to email list: " . $e->getMessage());
            throw $e;
        }
    }



}
