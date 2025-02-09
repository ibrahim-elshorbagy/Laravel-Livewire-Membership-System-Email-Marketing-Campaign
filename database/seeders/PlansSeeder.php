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
            'id'=> 1,
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
            'id'=> 2,
            'name' => 'Bronze Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 9,
        ]);
        $bronzeMonthly->features()->attach($subscribers, ['charges' => 1000]);
        $bronzeMonthly->features()->attach($emailSending, ['charges' => 5000]);

        $bronzeYearly = Plan::create([
            'id'=> 3,
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
            'id'=> 4,
            'name' => 'Silver Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 29,
        ]);
        $silverMonthly->features()->attach($subscribers, ['charges' => 5000]);
        $silverMonthly->features()->attach($emailSending, ['charges' => 20000]);

        $silverYearly = Plan::create([
            'id'=> 5,
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
            'id'=> 6,
            'name' => 'Golden Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 79,
        ]);
        $goldenMonthly->features()->attach($subscribers, ['charges' => 25000]);
        $goldenMonthly->features()->attach($emailSending, ['charges' => 50000]);

        $goldenYearly = Plan::create([
            'id'=> 7,
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
            'id'=> 8,
            'name' => 'Enterprise Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
            'price' => 199,
        ]);
        $enterpriseMonthly->features()->attach($subscribers, ['charges' => 50000]);
        $enterpriseMonthly->features()->attach($emailSending, ['charges' => 100000]);

        $enterpriseYearly = Plan::create([
            'id'=> 9,
            'name' => 'Enterprise Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity' => 1,
            'price' => 199 * 12,
        ]);
        $enterpriseYearly->features()->attach($subscribers, ['charges' => 50000 * 12]);
        $enterpriseYearly->features()->attach($emailSending, ['charges' => 100000 * 12]);

    }


}
