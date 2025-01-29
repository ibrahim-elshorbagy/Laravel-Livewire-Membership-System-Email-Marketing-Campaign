<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionCancelledNotification extends Notification
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
            ->subject('Subscription Cancellation')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('Your subscription has been cancelled by the administrator.')
            ->line('Subscription Details:')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Cancellation Date: ' . now()->format('F j, Y'))
            ->line('Your access will continue until: ' . $endDate)
            ->line('What happens next:')
            ->line('• You will continue to have access to all features until ' . $endDate)
            // ->line('• No further payments will be processed')
            // ->line('• Your subscription will not auto-renew')
            ->line('If you have any questions, please contact our support team.');
    }
}
