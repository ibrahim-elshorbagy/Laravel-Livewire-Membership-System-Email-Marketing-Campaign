<?php

namespace App\Mail;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationMail extends BaseMail
{
    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify.unauthenticated',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $mailData = [
            'slug' => 'user-email-verification',
            'user_id' => $user->id,
            'data' => [
                        'verification_url' => html_entity_decode($verifyUrl),
                        'url_expired_after' => '60 minute',
                ]
        ];

        parent::__construct($mailData);
    }
}
