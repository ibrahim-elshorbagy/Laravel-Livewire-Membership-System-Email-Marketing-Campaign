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





    public function getAllUnreadMessages(): array
    {
        // Initialize tracking with an estimate - we'll update the actual count later
        // Don't call initializeProgress here - let markUnreadMessages handle it

        $messages = [];
        $processedCount = 0;
        $matchingCount = 0;

        // Get all subject patterns
        $subjectPatterns = array_map('trim', $this->patterns['subject']);

        // Process each subject pattern separately for more efficient searching
        foreach ($subjectPatterns as $pattern) {
            // Use IMAP's built-in SUBJECT search criteria
            $searchCriteria = 'UNSEEN SUBJECT "' . $pattern . '"';
            $matchingEmails = imap_search($this->connection, $searchCriteria);

            //If no email search with next pattern
            if (!$matchingEmails) {
                continue;
            }

            Log::channel('emailBounces')->info("Found " . count($matchingEmails) . " unread messages with subject pattern: '$pattern'");

            // Update progress total if needed (first batch found)
            if ($processedCount == 0 && $this->jobProgress) {
                $this->updateProgressTotal(count($matchingEmails));
            }

            // Process the matching emails
            $headers = imap_fetch_overview($this->connection, implode(',', $matchingEmails), 0);

            foreach ($headers as $header) {
                $emailNumber = $header->msgno;
                $subject = isset($header->subject) ? imap_utf8($header->subject) : '';

                try {
                    $bounceHeaders = imap_fetchheader($this->connection, $emailNumber);
                    $bodyPreview = substr(imap_body($this->connection, $emailNumber), 0, 2000);
                    $bounceData = $bounceHeaders . "\n" . $bodyPreview;

                    $bounceType = $this->analyzeBounceMessage($bounceData);
                    $affectedEmail = $this->extractAffectedEmail($bodyPreview);

                    if (!empty($affectedEmail) && $bounceType) {
                        $messages[] = [
                            'number' => $emailNumber,
                            'subject' => $subject,
                            'bounce_type' => $bounceType,
                            'affected_email' => $affectedEmail,
                        ];
                        $matchingCount++;
                    }

                    imap_setflag_full($this->connection, $emailNumber, '\\Seen');
                    $processedCount++;

                    // Update progress after each item is processed
                    if ($this->jobProgress) {
                        $this->updateProgress($processedCount);
                    }

                } catch (\Exception $e) {
                    Log::channel('emailBounces')->error("Error processing email {$emailNumber}: " . $e->getMessage());
                    imap_setflag_full($this->connection, $emailNumber, '\\Seen');
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



    protected function extractAffectedEmail(string $body): ?string
    {
        // Match any non-space characters before @, followed by domain and TLD
        if (preg_match('/([^\s]+)@([^\s.]+)\.([^\s.]+)/', $body, $matches)) {
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

    
    protected function patternMatch(string $text, array $patterns): bool
    {
        $text = trim(strtolower($text));

        foreach ($patterns as $pattern) {
            $pattern = trim(strtolower($pattern));

            if ($text === $pattern || stripos($text, $pattern) !== false) {
                Log::channel('emailBounces')->info("Subject matched pattern: '{$pattern}' in text:");// '{$text}'
                return true;
            }
        }

        Log::channel('emailBounces')->debug("No match for subject text: "); // '{$text}'
        return false;
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
