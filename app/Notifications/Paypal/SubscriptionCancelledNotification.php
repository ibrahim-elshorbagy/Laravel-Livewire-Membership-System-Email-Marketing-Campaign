<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelledNotification extends Notification
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
        $endDate = $this->subscription->expired_at->addMonth()->format('F j, Y');

        return (new MailMessage)
            ->subject('Subscription Cancellation Confirmation')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('We\'re sorry to see you go. Your subscription cancellation has been processed successfully.')
            ->line('Subscription Details:')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Cancellation Date: ' . now()->format('F j, Y'))
            ->line('Your access will continue until: ' . $endDate)
            ->line('What happens next:')
            ->line('• You will continue to have access to all features until ' . $endDate)
            // ->line('• No further payments will be processed')
            // ->line('• Your subscription will not auto-renew')
            // ->action('Reactivate Subscription', config('app.after_cancel_payment_url'))
            ->line('We hope to see you again soon! If you\'d like to reactivate your subscription, you can do so at any time.')
            ->line('If you cancelled by mistake or have any questions, please contact our support team.');
    }
}
