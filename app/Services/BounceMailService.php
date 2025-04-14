<?php

namespace App\Services;

use App\Models\UserBouncesInfo;
use App\Models\Admin\Site\SystemSetting\BouncePattern;

class BounceMailService
{
    private $mailbox;
    private $connection;
    private $user;

    protected $subjectPatterns = [];
    protected $hardBouncePatterns = [];
    protected $softBouncePatterns = [];

    public function __construct(UserBouncesInfo $bounceInfo)
    {
        $this->user = $bounceInfo;
        $this->loadBouncePatterns();
    }

    protected function loadBouncePatterns(): void
    {
        $this->subjectPatterns = BouncePattern::getPatterns('subject');
        $this->hardBouncePatterns = BouncePattern::getPatterns('hard');
        $this->softBouncePatterns = BouncePattern::getPatterns('soft');
    }



    public function connect(): bool
    {
        if (!extension_loaded('imap')) {
            throw new \Exception('PHP IMAP extension is not installed or enabled. Please enable it in your php.ini');
        }

        // Add timeout options to the connection string
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
        }
    }

    public function getUnreadMessages(): array
    {
        if (!$this->connection) {
            throw new \Exception('No active IMAP connection');
        }

        // First try to find bounce messages by subject
        $emails = null;
        foreach ($this->subjectPatterns as $pattern) {
            $searchQuery = 'UNSEEN SUBJECT "' . str_replace('"', '', $pattern) . '"';
            $result = imap_search($this->connection, $searchQuery);

            if ($result) {
                $emails = $result;
                break;
            }
        }



        if (!$emails) {
            return [];
        }


        $messages = [];
        foreach ($emails as $emailNumber) {
            $header = imap_headerinfo($this->connection, $emailNumber);

            // Get headers and body preview for efficient analysis
            $bounceHeaders = imap_fetchheader($this->connection, $emailNumber);
            $bodyPreview = substr(imap_body($this->connection, $emailNumber), 0, 2000);

            // Combine for analysis
            $bounceData = $bounceHeaders . "\n" . $bodyPreview;

            // Analyze the bounce message
            $bounceType = $this->analyzeBounceMessage($bounceData);

            // Extract affected email more effectively
            $affectedEmail = $this->extractAffectedEmail($bodyPreview);

            $messages[] = [
                'number' => $emailNumber,
                'subject' => $header->subject ?? '',
                'from' => $header->from[0]->mailbox . '@' . $header->from[0]->host,
                'date' => $header->date,
                'bounce_type' => $bounceType,
                'affected_email' => $affectedEmail,
                'is_read' => false
            ];

            // Mark as read
            imap_setflag_full($this->connection, $emailNumber, '\\Seen');
        }

        return $messages;
    }

    /**
     * Extract the affected email address from bounce message
     *
     * @param string $data The email content to analyze
     * @return string|null The affected email address or null if not found
     */
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
        // Check for hard bounce patterns
        foreach ($this->hardBouncePatterns as $pattern) {
            if (stripos($data, $pattern) !== false) {
                return 'hard';
            }
        }

        // Check for soft bounce patterns
        foreach ($this->softBouncePatterns as $pattern) {
            if (stripos($data, $pattern) !== false) {
                return 'soft';
            }
        }

        return null; // No bounce pattern found
    }
}
