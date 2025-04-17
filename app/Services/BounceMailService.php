<?php

namespace App\Services;

use App\Models\UserBouncesInfo;
use App\Models\Admin\Site\SystemSetting\BouncePattern;
use App\Models\EmailList;
use App\Models\JobProgress;
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

    public function __construct(UserBouncesInfo $bounceInfo, $jobId = null)
    {
        $this->user = $bounceInfo;
        $this->jobId = $jobId;
        $this->loadBouncePatterns();
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
                $updates = [
                    'hard' => [],
                    'soft' => []
                ];

                foreach ($messages as $index => $message) {
                    if (!empty($message['affected_email'])) {
                        if ($message['bounce_type'] === 'hard') {
                            $updates['hard'][] = $message['affected_email'];
                        } elseif ($message['bounce_type'] === 'soft') {
                            $updates['soft'][] = $message['affected_email'];
                        }
                    }

                    // Update progress for each categorized message
                    $this->updateProgress($index + 1);
                }

                $this->processHardBounces($updates['hard']);
                $this->processSoftBounces($updates['soft']);
            }

            $this->completeProgress();

        } catch (\Exception $e) {
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }



    protected function processHardBounces(array $emails): void
    {
        if (empty($emails)) {
            return;
        }

        EmailList::where('user_id', $this->user->user_id)
            ->whereIn('email', $emails)
            ->update(['is_hard_bounce' => true]);

        Log::channel('emailBounces')->info('Updated ' . count($emails) . ' hard bounces');
    }



    protected function processSoftBounces(array $emails): void
    {
        if (empty($emails)) {
            return;
        }

        DB::beginTransaction();
        try {
            $emailsToCheck = EmailList::where('user_id', $this->user->user_id)
                ->whereIn('email', $emails)
                ->select('email', 'soft_bounce_counter')
                ->get()
                ->keyBy('email');

            EmailList::where('user_id', $this->user->user_id)
                ->whereIn('email', $emails)
                ->increment('soft_bounce_counter');

            $hardBounceEmails = [];
            foreach ($emails as $email) {
                if (isset($emailsToCheck[$email]) &&
                    ($emailsToCheck[$email]->soft_bounce_counter + 1) >= $this->user->max_soft_bounces) {
                    $hardBounceEmails[] = $email;
                }
            }

            if (!empty($hardBounceEmails)) {
                $this->processHardBounces($hardBounceEmails);
                Log::channel('emailBounces')->info('Converted ' . count($hardBounceEmails) . ' soft bounces to hard bounces');
            }

            DB::commit();

            Log::channel('emailBounces')->info('Updated ' . count($emails) . ' soft bounces');

        } catch (\Exception $e) {
            DB::rollBack();
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
}
