<?php

namespace App\Mail;

use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;

class SupportResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {

        $emailTemplate = SystemEmail::where('slug', 'support-ticket-admin-response')->first();

        // Check if template was found
        if (!$emailTemplate) {
            // Use fallback template
            return $this->subject($this->data['subject'])
                ->view('emails.support-fallback', [
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

        // Get the HTML template
        $templateHtml = $emailTemplate->message_html;

        // Create data array with all variables needed in the template
        $data = [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'subject' => $emailTemplate->email_subject,
            'messageContent' => $this->data['message']
        ];

        // Render the template string directly with the data
        $renderedHtml = html_entity_decode(Blade::render($templateHtml, $data));

        return $this->subject($this->data['subject'])
            ->html($renderedHtml)
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
