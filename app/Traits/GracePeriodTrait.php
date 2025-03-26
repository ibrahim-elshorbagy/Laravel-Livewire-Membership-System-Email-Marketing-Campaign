<?php

namespace App\Traits;

use App\Models\Admin\Site\SiteSetting;
use Carbon\Carbon;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;

trait GracePeriodTrait
{
    /**
     * Calculate the grace period end date based on site settings.
     *
     * @param string|null $expiredAt
     * @return string|null
     */
    public function calculateGracePeriodEnd(?string $expiredAt): ?string
    {
        if (!$expiredAt) {
            return null;
        }

        $graceDays = (int) SiteSetting::getValue('grace_days', 0);
        return Carbon::parse($expiredAt)->addDays($graceDays)->toDateTimeString();
    }

    /**
     * Wrapper method to apply grace period using the subscribeTo method.
     *
     * @param Plan $plan
     * @param string|null $expiration
     * @param string|null $startDate
     * @return Subscription
     * @throws \Exception
     */
    public function graceSubscribeTo(Plan $plan, $expiration = null, $startDate = null): Subscription
    {

        // Subscribe to the plan using the HasSubscriptions method
        $subscription = $this->subscribeTo($plan, $expiration, $startDate);

        // Calculate and apply grace period
        $subscription->grace_days_ended_at = $this->calculateGracePeriodEnd($subscription->expired_at);
        $subscription->save();

        return $subscription;
    }
}
