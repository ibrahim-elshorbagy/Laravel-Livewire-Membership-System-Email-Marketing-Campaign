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
    public function run(): void
    {

        // Features
        $emailMarketingCampaigns = Feature::create([
            'consumable'       => true,
            'name'             => 'Email Marketing Campaigns',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
        ]);

                // Features
        $test = Feature::create([
            'consumable'       => true,
            'name'             => 'Test',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
        ]);

        // ------------------------------------------------------------------------------------------------
        // Plans bronze
        $bronzeMonthly = Plan::create([
            'name'             => 'Bronze Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price'            => 3,
        ]);
        $bronzeMonthly->features()->attach($emailMarketingCampaigns,['charges'=>5]);

        $bronzeYearly = Plan::create([
            'name'             => 'Bronze Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity'      => 1,
            'price'            => 3 * 12,
        ]);
        $bronzeYearly->features()->attach($emailMarketingCampaigns,['charges'=>5 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans silver

        $silverMonthly = Plan::create([
            'name'             => 'Silver Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price'            => 5,
        ]);
        $silverMonthly->features()->attach($emailMarketingCampaigns,['charges'=>10]);

        $silverYearly = Plan::create([
            'name'             => 'Silver Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity'      => 1,
            'price'            => 5 * 12,
        ]);
        $silverYearly->features()->attach($emailMarketingCampaigns,['charges'=>10 * 12]);


        // ------------------------------------------------------------------------------------------------
        // Plans golden

        $goldenMonthly = Plan::create([
            'name'             => 'Golden Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price'            => 10,
        ]);
        $goldenMonthly->features()->attach($emailMarketingCampaigns,['charges'=>20]);

        $goldenYearly = Plan::create([
            'name'             => 'Golden Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity'      => 1,
            'price'            => 10 * 12,
        ]);
        $goldenYearly->features()->attach($emailMarketingCampaigns,['charges'=>20 * 12]);


        // ------------------------------------------------------------------------------------------------
        // Plans diamond
        $diamondMonthly = Plan::create([
            'name'             => 'Diamond Monthly',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price'            => 15,
        ]);
        $diamondMonthly->features()->attach($emailMarketingCampaigns,['charges'=>30]);

        $diamondYearly = Plan::create([
            'name'             => 'Diamond Yearly',
            'periodicity_type' => PeriodicityType::Year,
            'periodicity'      => 1,
            'price'            => 15 * 12,
        ]);
        $diamondYearly->features()->attach($emailMarketingCampaigns,['charges'=>30 * 12]);


    }
}
