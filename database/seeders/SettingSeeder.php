<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            DB::table('site_settings')->insert([
            ['property' => 'site_name', 'value' => 'GeMailAPP'],
            ['property' => 'company_logo', 'value' => 'https://cdn-icons-png.flaticon.com/512/5968/5968534.png'],
            ['property' => 'support_email', 'value' => 'support@mywebsite.com'],
            ['property' => 'support_phone', 'value' => '+1234567890'],
        ]);
    }
}
