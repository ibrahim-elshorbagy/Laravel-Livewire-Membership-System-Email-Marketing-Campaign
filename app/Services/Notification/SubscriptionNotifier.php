<?php
namespace App\Services\Notification;

use App\Models\User;
use MBarlow\Megaphone\Types\Important;
use App\Models\Admin\Site\SiteSetting;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\BaseMail;
use Illuminate\Support\Facades\Log;

class SubscriptionNotifier
{



    public function SubscriptionAboutToEndNotify()
    {
        // Get notification settings from site settings
        $notifyDays = (int)SiteSetting::getValue('subscription_notify_days') ?? 3;
        $notifyTitle = SiteSetting::getValue('subscription_notify_title') ?? 'Subscription Expiring Soon!';
        $notifyMessage = SiteSetting::getValue('subscription_notify_message') ?? 'Your subscription will expire soon. Please renew to maintain access to all features.';

        $expiryDate = now()->addDays($notifyDays);

        // Get all subscriptions that will expire within configured days and haven't been notified
        $subscriptions = \LucasDotVin\Soulbscription\Models\Subscription::query()
            ->whereNull('canceled_at')
            ->whereNull('About_to_end_notify_sent_at')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $expiryDate)
            ->where('expired_at', '>', now())
            ->with('subscriber')
            ->get();

            $slug ='notification-subscription-about-to-end';
            $emailTemplate = SystemEmail::where('slug', $slug)->select('id')->first();

        foreach ($subscriptions as $subscription) {
            $user = $subscription->subscriber;

            // Create notification with configured title and message
            $notification = new Important(
                $notifyTitle,
                $notifyMessage
            );

            // Send notification
            $user->notify($notification);

            // Send Email notification
            if ($emailTemplate) {
                $mailData = [
                    'slug' => $slug,
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                ];
                Log::info('Mail data sent', $mailData);

                Mail::to($user->email)->queue(new BaseMail($mailData));

            }

            // Mark notification as sent
            $subscription->update([
                'About_to_end_notify_sent_at' => now()
            ]);
        }
    }




}
