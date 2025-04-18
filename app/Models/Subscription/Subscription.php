<?php

namespace App\Models\Subscription;

use App\Models\Payment\Payment;
use LucasDotVin\Soulbscription\Models\Subscription as BaseSubscription;

class Subscription extends BaseSubscription
{
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function note()
    {
        return $this->hasOne(Note::class);
    }

    public function getFeatureData()
    {
        // Return empty array if no subscriber exists
        if (!$this->subscriber) {
            return collect();
        }


        return $this->plan->features->mapWithKeys(function ($feature) {
            $consumption = optional($this->subscriber)->featureConsumptions
                ?->first(function ($consumption) use ($feature) {
                    return optional($consumption->feature)->name === $feature->name;
                });

            return [
                $feature->name => [
                    'limit' => $feature->pivot->charges,
                    'used' => $consumption ? $consumption->consumption : 0
                ]
            ];
        });
    }

}
