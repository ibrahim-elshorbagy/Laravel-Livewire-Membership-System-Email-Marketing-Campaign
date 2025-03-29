<?php

namespace App\Traits;

use Carbon\Carbon;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;

trait PlanPriceCalculator
{


    protected function calculateUpgradeCost($newPlan)
    {
        $user = auth()->user();
        $currentSubscription = $user->lastSubscription();
        $newPlan = Plan::findOrFail($newPlan);

        if($newPlan && $user)
        {

            if ($currentSubscription) { //If there is Subscription

                return $this->calculateUpgradePrice($newPlan, $currentSubscription);

            }else{ //If there is No Subscription At all
                    $days = $newPlan->periodicity_type;
                    $totalPeriodDays = $days === 'Year' ? 365 : 30;

                    return [
                        'title' => 'Subscribe',
                        'upgrade_cost' => round($newPlan->price, 2),
                        'remaining_days' => 0,
                        'unused_amount' => 0,
                        'new_daily_rate' => round($newPlan->price / $totalPeriodDays, 2),
                        'current_daily_rate' => 0,
                        'totalPeriodDays'=> $totalPeriodDays,
                    ];
            }

        }
    }



    public function calculateUpgradePrice(Plan $newPlan, Subscription $currentSubscription): ?array
    {


        $currentPlan = $currentSubscription->plan;
        $startDate = $currentSubscription->started_at;
        $endDate = $currentSubscription->expired_at;
        $now = Carbon::now();

        // Calculate total period and consumed days for current subscription
        $currentTotalPeriodDays = floor($startDate->diffInDays($endDate));
        $consumedDays = floor($startDate->diffInDays($now));
        $remainingDays = max(0, $currentTotalPeriodDays - $consumedDays);

        // Calculate new plan's total period days
        $newTotalPeriodDays = $newPlan->periodicity_type === 'Year' ? 365 : 30;

        // Calculate daily rates
        $currentDailyRate = $currentPlan->price / $currentTotalPeriodDays;
        $newDailyRate = $newPlan->price / $newTotalPeriodDays;

        // Calculate unused amount from current plan
        $unusedAmount = $currentPlan->price - ($currentDailyRate * $consumedDays);

        // Calculate cost for remaining days at new plan rate
        $remainingCost = $newDailyRate * $remainingDays;

        // Calculate final upgrade cost
        $upgradeCost = max(0, $remainingCost - $unusedAmount);
        $title = "Upgrade";

        // Only calculate if upgrading to a higher-priced plan
        if ($newPlan->price <= $currentSubscription->plan->price) {
            $upgradeCost = $newPlan->price;
            $title = "Downgrade";
        }

        // Calculate will_started_at and will_expired_at based on subscription change logic
        $dates = $this->calculateSubscriptionDates($newPlan, $currentPlan, $startDate, $endDate, $upgradeCost);
        $willStartedAt = $dates['will_started_at'];
        $willExpiredAt = $dates['will_expired_at'];

        return [
            'title' => $title,
            'upgrade_cost' => round($upgradeCost, 2),
            'remaining_days' => $remainingDays,
            'unused_amount' => round($unusedAmount, 2),
            'new_daily_rate' => round($newDailyRate, 2),
            'current_daily_rate' => round($currentDailyRate, 2),
            'totalPeriodDays' => $currentTotalPeriodDays,
            'will_started_at' => $willStartedAt,
            'will_expired_at' => $willExpiredAt
        ];
    }

    protected function calculateSubscriptionDates(Plan $newPlan, Plan $currentPlan, Carbon $startDate, Carbon $endDate, float $upgradeCost): array
    {
        $willStartedAt = now();
        $willExpiredAt = now();

        // If upgrading to a higher-priced yearly plan, keep original dates
        if ($newPlan->periodicity_type === 'Year' && $currentPlan->periodicity_type === 'Year' && $newPlan->price >= $currentPlan->price) {
            $willStartedAt = $startDate;
            $willExpiredAt = $endDate;
        }
        // If upgrading from monthly to yearly
        else if ($newPlan->periodicity_type === 'Year' && $currentPlan->periodicity_type !== 'Year') {
            $willStartedAt = $startDate;
            $willExpiredAt = $endDate->copy()->addYear();
        }
        // If it's a pro-rated upgrade (partial payment)
        else if ($upgradeCost < $newPlan->price) {
            $willStartedAt = $startDate;
            $willExpiredAt = $endDate;
        }
        // For all other cases (downgrades or full new subscription period)
        else {
            $willStartedAt = now();
            $willExpiredAt = $newPlan->periodicity_type === 'Year' ? now()->addYear() : now()->addMonth();
        }

        return [
            'will_started_at' => $willStartedAt,
            'will_expired_at' => $willExpiredAt
        ];
    }

}
