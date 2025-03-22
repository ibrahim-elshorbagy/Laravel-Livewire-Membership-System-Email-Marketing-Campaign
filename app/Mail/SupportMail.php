<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        // Debug logging
        // logger('Mail attachments:', $this->data['attachments'] ?? []);

        return $this->subject($this->data['subject'])
            ->view('emails.support', [
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'subject' => $this->data['subject'],
                'messageContent' => $this->data['message']
            ])
            ->withSymfonyMessage(function ($message) {
                if (!empty($this->data['attachments'])) {
                    foreach ($this->data['attachments'] as $attachment) {
                        $message->embedFromPath(
                            $attachment['path'],
                            $attachment['name']
                        );
                    }
                }
            });
    }

    protected function attachImages()
    {
        if (empty($this->data['attachments'])) return $this;

        foreach ($this->data['attachments'] as $attachment) {
            $this->embedData(
                file_get_contents($attachment['path']),
                $attachment['name'], // Use the stored filename
                [
                    'mime' => mime_content_type($attachment['path']),
                    'cid' => $attachment['name'] // Must match CID in HTML
                ]
            );
        }

        return $this;
    }

}
