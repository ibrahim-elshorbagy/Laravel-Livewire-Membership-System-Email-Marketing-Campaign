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

        // Calculate total period and consumed days
        $totalPeriodDays = floor($startDate->diffInDays($endDate));
        $consumedDays = floor($startDate->diffInDays($now));
        $remainingDays = max(0, $totalPeriodDays - $consumedDays);

        // Calculate daily rates
        $currentDailyRate = $currentPlan->price / $totalPeriodDays;
        $newDailyRate = $newPlan->price / $totalPeriodDays;

        // Calculate unused amount from current plan
        $unusedAmount = $currentPlan->price - ($currentDailyRate * $consumedDays);

        // Calculate cost for remaining days at new plan rate
        $remainingCost = $newDailyRate * $remainingDays;

        // Calculate final upgrade cost
        $upgradeCost = max(0, $remainingCost - $unusedAmount);
        $title =  "Upgrade";
        // Only calculate if upgrading to a higher-priced plan
        if ($newPlan->price <= $currentSubscription->plan->price) {
            $upgradeCost = $newPlan->price;
            $title =  "Downgrade";
        }


        return [
            'title' => $title,
            'upgrade_cost' => round($upgradeCost, 2),
            'remaining_days' => $remainingDays,
            'unused_amount' => round($unusedAmount, 2),
            'new_daily_rate' => round($newDailyRate, 2),
            'current_daily_rate' => round($currentDailyRate, 2),
            'totalPeriodDays'=>$totalPeriodDays,
        ];
    }
}
