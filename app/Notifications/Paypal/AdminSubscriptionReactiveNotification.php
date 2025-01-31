<?php

namespace App\Notifications\Paypal;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSubscriptionReactiveNotification extends Notification implements ShouldQueue
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
            ->subject('Subscription Reactivation')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . '!')
            ->line('We are pleased to inform you that your subscription has been reactivated by the administrator.')
            ->line('Subscription Details:')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Reactivation Date: ' . now()->format('F j, Y'))
            ->when($this->subscription->plan->name !== 'Trial', function (MailMessage $mailMessage) {
                return $mailMessage->line('Your access is now restored and will continue until: ' . $this->subscription->expired_at->format('F j, Y'));
            })
            ->line('Enjoy all the features of your plan!')
            ->line('If you have any questions, please contact our support team.');
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
