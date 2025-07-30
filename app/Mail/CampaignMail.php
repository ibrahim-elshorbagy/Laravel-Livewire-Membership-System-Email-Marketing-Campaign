<?php

namespace App\Mail;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class CampaignMail extends BaseMail
{
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $mailData = [
            'slug' => $data['slug'],
            'user_id' => $data['user_id'],
            'data' => $data['data'] ?? []
        ];

        parent::__construct($mailData);
    }
}

