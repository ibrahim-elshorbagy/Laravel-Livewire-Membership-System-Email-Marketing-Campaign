<?php

namespace App\Mail;

use Illuminate\Support\Facades\Password;
use App\Models\User;

class SendResetLinkMail extends BaseMail
{
    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        // Convert stdClass to User model instance if needed
        if (!$user instanceof User) {
            $user = User::find($user->id);
        }

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $mailData = [
            'slug' => 'user-forgot-password',
            'user_id' => $user->id,
            'data' => [
                'reset_url' => $resetUrl,
                'url_expired_after' => '60 minutes',
            ]
        ];

        parent::__construct($mailData);
    }
}
