<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Feature;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Features
        $subscribers = Feature::create([
            'consumable' => true,
            'quota'      => true,
            'name' => 'Subscribers Limit',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        $emailSending = Feature::create([
            'consumable' => true,
            'name' => 'Email Sending',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);


        // ------------------------------------------------------------------------------------------------
        // Free Plan
        $freePlan = Plan::create([
            'name' => 'Trial',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 10,
            'price' => 0,
        ]);
        $freePlan->features()->attach($subscribers, ['charges' => 250]);
        $freePlan->features()->attach($emailSending, ['charges' => 500]);

        // ------------------------------------------------------------------------------------------------
        // Plans bronze
        $bronzeMonthly = Plan::create([
            'name' => 'Bronze Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 9,
        ]);
        $bronzeMonthly->features()->attach($subscribers, ['charges' => 1000]);
        $bronzeMonthly->features()->attach($emailSending, ['charges' => 5000]);

        $bronzeYearly = Plan::create([
            'name' => 'Bronze Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 1,
            'price' => 9 * 12,
        ]);
        $bronzeYearly->features()->attach($subscribers, ['charges' => 1000 * 12]);
        $bronzeYearly->features()->attach($emailSending, ['charges' => 5000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans silver
        $silverMonthly = Plan::create([
            'name' => 'Silver Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 29,
        ]);
        $silverMonthly->features()->attach($subscribers, ['charges' => 5000]);
        $silverMonthly->features()->attach($emailSending, ['charges' => 20000]);

        $silverYearly = Plan::create([
            'name' => 'Silver Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 1,
            'price' => 29 * 12,
        ]);
        $silverYearly->features()->attach($subscribers, ['charges' => 5000 * 12]);
        $silverYearly->features()->attach($emailSending, ['charges' => 20000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans golden
        $goldenMonthly = Plan::create([
            'name' => 'Golden Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 79,
        ]);
        $goldenMonthly->features()->attach($subscribers, ['charges' => 25000]);
        $goldenMonthly->features()->attach($emailSending, ['charges' => 50000]);

        $goldenYearly = Plan::create([
            'name' => 'Golden Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 1,
            'price' => 79 * 12,
        ]);
        $goldenYearly->features()->attach($subscribers, ['charges' => 25000 * 12]);
        $goldenYearly->features()->attach($emailSending, ['charges' => 50000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans enterprise
        $enterpriseMonthly = Plan::create([
            'name' => 'Enterprise Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 199,
        ]);
        $enterpriseMonthly->features()->attach($subscribers, ['charges' => 50000]);
        $enterpriseMonthly->features()->attach($emailSending, ['charges' => 100000]);

        $enterpriseYearly = Plan::create([
            'name' => 'Enterprise Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 1,
            'price' => 199 * 12,
        ]);
        $enterpriseYearly->features()->attach($subscribers, ['charges' => 50000 * 12]);
        $enterpriseYearly->features()->attach($emailSending, ['charges' => 100000 * 12]);

    }


}
