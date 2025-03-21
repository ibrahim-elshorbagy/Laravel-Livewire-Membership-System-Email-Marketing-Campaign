<?php

namespace App\Traits;

use App\Models\Payment\Payment;
use App\Notifications\Paypal\SubscriptionActivatedNotification;
use App\Notifications\Paypal\SubscriptionRenewedNotification;
use App\Services\PayPalLogger;

trait SubscriptionManagementTrait
{
    public function handleSubscriptionChange(Payment $payment)
    {
        if ($payment->user->lastSubscription()) {
            // Case 1: User is renewing the same plan
            if ($payment->user->lastSubscription()->plan->id == $payment->plan_id) {
                $subscription = $payment->user->lastSubscription();

                // Check if it's an annual plan
                if ($payment->plan->periodicity == 'Year') {
                    $subscription->update([
                        'expired_at' => $subscription->expired_at->copy()->addYear()
                    ]);
                } else {
                    $subscription->update([
                        'expired_at' => $subscription->expired_at->copy()->addMonth()
                    ]);
                }

                $payment->user->notify(new SubscriptionRenewedNotification($subscription));
                return $subscription;
            }
            // Case 2: User is changing to a different plan
            else {
                // Get existing subscription info before suppressing it
                $oldSubscription = $payment->user->lastSubscription();
                $oldPlan = $oldSubscription->plan;
                $started_at = $oldSubscription->started_at;
                $expired_at = $oldSubscription->expired_at;

                // Check if current subscription is a trial plan (10-year plan)
                $isTrial = $oldPlan->id == 1;

                // Suppress  the old subscription
                $oldSubscription->suppress();

                // Create new subscription
                $subscription = $payment->user->subscribeTo($payment->plan);
                $newSubscription = $payment->user->lastSubscription();
                $newSubscription->update(['started_at' => now()]);

                // If coming from a trial plan, treat it as a new subscription
                if ($isTrial) {
                    // No changes needed - default dates will be used (starts today)
                    $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                    return $subscription;
                }
                // Case 2.1: User is upgrading to any YEARLY plan (and not from trial)
                else if ($payment->plan->periodicity_type == 'Year') {
                    // If changing from yearly plan to another yearly plan
                    if ($oldPlan->periodicity_type == 'Year') {
                        // If it's an upgrade to a higher-priced yearly plan
                        if ($payment->plan->price >= $oldPlan->price) {
                            // Keep original dates exactly as they were
                            $newSubscription->update([
                                'started_at' => $started_at,
                                'expired_at' => $expired_at
                            ]);
                            $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                            return $subscription;
                        }
                        // If it's a downgrade to a lower-priced yearly plan
                        else {
                            // Keep the new subscription dates
                            // No update needed as the system already sets appropriate defaults
                            $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                            return $subscription;
                        }
                    }
                    // If upgrading from monthly to yearly
                    else {
                        // Add a year to the current expiration date
                        $newSubscription->update([
                            'started_at' => $started_at,
                            'expired_at' => $expired_at->copy()->addYear()
                        ]);
                        $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                        return $subscription;
                    }
                }
                // Case 2.2: User is changing to a MONTHLY plan (and not from trial)
                else {
                    // If it's a pro-rated upgrade (partial payment)
                    if ($payment->amount < $payment->plan->price) {
                        // Maintain previous dates for prorated upgrades
                        $newSubscription->update([
                            'started_at' => $started_at,
                            'expired_at' => $expired_at
                        ]);
                        $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                        return $subscription;
                    }
                    // If downgrading to a lower plan (full payment)
                    else {
                        // Keep new subscription dates (default behavior)
                        // This means they pay full price for the new plan duration
                        // No changes needed here as the system already sets default dates
                        $payment->user->notify(new SubscriptionActivatedNotification($subscription));
                        return $subscription;
                    }
                }
            }
        }
        else {
            // There is no subscription at all - first time user
            $subscription = $payment->user->subscribeTo($payment->plan);
            $subscription->update(['started_at' => now()]);
            $payment->user->notify(new SubscriptionActivatedNotification($subscription));
            return $subscription;
        }
    }
}
