<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Subscription Renewed Successfully')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('Your subscription has been renewed successfully.')
            ->line('Subscription Details:')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Duration: ' . $this->subscription->plan->periodicity_type)
            ->line('Amount: $' . number_format($this->subscription->plan->price, 2))
            ->line('Next Billing Date: ' . ($this->subscription->expired_at ? $this->subscription->expired_at->format('F j, Y') : 'Not Available'))
            // ->action('View Subscription Details', url('/dashboard/subscription'))
            ->line('Thank you for continuing your subscription with us!')
            ->line('If you have any questions, please don\'t hesitate to contact our support team.');
    }
}
