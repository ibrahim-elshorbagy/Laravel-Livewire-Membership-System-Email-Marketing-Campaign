<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivatedNotification extends Notification
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
            ->subject('Subscription Activated Successfully')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('Your subscription has been activated successfully.')
            ->line('Subscription Details:')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Duration: ' . $this->subscription->plan->periodicity_type)
            ->line('Amount: $' . number_format($this->subscription->plan->price, 2))
            ->line('Start Date: ' . $this->subscription->created_at->format('F j, Y'))
            ->line('Next Billing Date: ' . $this->subscription->created_at->addMonth()->format('F j, Y'))
            ->line('Your subscription will automatically renew each ' . strtolower($this->subscription->plan->periodicity_type) . '.')
            // ->action('View Subscription Details', config('app.after_success_payment_url'))
            ->line('Thank you for subscribing to our service!')
            ->line('If you have any questions, please don\'t hesitate to contact our support team.');
    }

}
