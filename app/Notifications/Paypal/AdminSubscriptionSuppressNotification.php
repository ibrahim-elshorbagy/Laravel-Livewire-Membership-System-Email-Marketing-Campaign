<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionSuppressNotification extends Notification
{

    use Queueable;

    protected $subscription;

    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $endDate = $this->subscription->created_at->addMonth()->format('F j, Y');

        return (new MailMessage)
            ->subject('Subscription Suppression')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('The administrator has suppressed your subscription.')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('As a result, you will no longer have access to the subscription features.')
            ->line('If you have any questions or believe this was a mistake, please contact our support team.');
    }
}
