<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Feature;

class AiFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $aiGenerating = Feature::create([
            'consumable' => true,
            'quota'      => true,
            'name' => 'AI Email Builder',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity' => 1,
        ]);

        // ------------------------------------------------------------------------------------------------
        // Free Plan
        $freePlan = Plan::find(1);
        $freePlan?->features()->attach($aiGenerating, ['charges' => 500]);

        // ------------------------------------------------------------------------------------------------
        // Plans bronze
        $bronzeMonthly = Plan::find(2);
        $bronzeMonthly?->features()->attach($aiGenerating, ['charges' => 5000]);

        $bronzeYearly = Plan::find(3);
        $bronzeYearly?->features()->attach($aiGenerating, ['charges' => 5000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans silver
        $silverMonthly = Plan::find(4);
        $silverMonthly?->features()->attach($aiGenerating, ['charges' => 20000]);

        $silverYearly = Plan::find(5);
        $silverYearly?->features()->attach($aiGenerating, ['charges' => 20000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans golden
        $goldenMonthly = Plan::find(6);
        $goldenMonthly?->features()->attach($aiGenerating, ['charges' => 50000]);

        $goldenYearly = Plan::find(7);
        $goldenYearly?->features()->attach($aiGenerating, ['charges' => 50000 * 12]);

        // ------------------------------------------------------------------------------------------------
        // Plans enterprise
        $enterpriseMonthly = Plan::find(8);
        $enterpriseMonthly?->features()->attach($aiGenerating, ['charges' => 100000]);

        $enterpriseYearly = Plan::find(9);
        $enterpriseYearly?->features()->attach($aiGenerating, ['charges' => 100000 * 12]);
    }
}
