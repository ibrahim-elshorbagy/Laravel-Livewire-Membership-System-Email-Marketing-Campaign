<?php

namespace App\Mail;

class BaseSupportMail extends BaseMail
{
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $mailData = [
            'slug' => $data['slug'],
            'user_id' => $data['user_id'],
            'data' => [
                'ticket_id' => $data['ticket_id'],
                'subject' => $data['subject']
            ]
        ];

        parent::__construct($mailData);
    }
    
}
