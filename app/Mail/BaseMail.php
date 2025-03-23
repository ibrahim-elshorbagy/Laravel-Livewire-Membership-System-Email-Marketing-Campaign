<?php

namespace App\Mail;

use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class BaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        // Get email template from database
        $emailInfo = SystemEmail::where('slug', $this->data['slug'])
            ->firstOrFail();

        // Build email with dynamic content
        $mail = $this->subject($this->data['subject'])
            ->view('emails.base', [
                'htmlContent' => $emailInfo->message_html,
                'plainContent' => $emailInfo->message_plain_text,
                'data' => $this->data
            ])
            ->text('emails.base_plain', [
                'plainContent' => $emailInfo->message_plain_text,
                'data' => $this->data
            ]);

        // Handle CID-based attachments
        if (!empty($this->data['attachments'])) {
            $this->attachImages();
        }

        return $mail;
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
