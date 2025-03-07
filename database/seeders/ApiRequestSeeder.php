<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiRequestSeeder extends Seeder
{
    public function run(): void
    {
        $serverIds = ['server1', 'server2', 'server3'];
        $now = Carbon::now();

        // Generate hourly data for the last 24 hours
        for ($i = 23; $i >= 0; $i--) {
            $time = $now->copy()->subHours($i);
            $this->generateRequestsForHour($time, $serverIds);
        }

        // Generate daily data for the last 30 days
        for ($i = 29; $i >= 1; $i--) {
            $time = $now->copy()->subDays($i);
            $this->generateRequestsForDay($time, $serverIds);
        }

        // Generate weekly data for the last 12 weeks
        for ($i = 11; $i >= 1; $i--) {
            $time = $now->copy()->subWeeks($i);
            $this->generateRequestsForWeek($time, $serverIds);
        }

        // Generate monthly data for the last 12 months
        for ($i = 11; $i >= 1; $i--) {
            $time = $now->copy()->subMonths($i);
            $this->generateRequestsForMonth($time, $serverIds);
        }
    }

    private function generateRequestsForHour(Carbon $time, array $serverIds): void
    {
        $requestCount = rand(50, 200);
        for ($i = 0; $i < $requestCount; $i++) {
            DB::table('api_requests')->insert([
                'serverid' => $serverIds[array_rand($serverIds)],
                'execution_time' => rand(100, 2000) / 100, // Random time between 1-20ms
                'status' => rand(1, 10) > 1 ? 'success' : 'failed',
                'request_time' => $time->copy()->addMinutes(rand(0, 59))->addSeconds(rand(0, 59)),
            ]);
        }
    }

    private function generateRequestsForDay(Carbon $time, array $serverIds): void
    {
        $requestCount = rand(500, 2000);
        for ($i = 0; $i < $requestCount; $i++) {
            DB::table('api_requests')->insert([
                'serverid' => $serverIds[array_rand($serverIds)],
                'execution_time' => rand(100, 2000) / 100,
                'status' => rand(1, 10) > 1 ? 'success' : 'failed',
                'request_time' => $time->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59))->addSeconds(rand(0, 59)),
            ]);
        }
    }

    private function generateRequestsForWeek(Carbon $time, array $serverIds): void
    {
        $requestCount = rand(2000, 5000);
        for ($i = 0; $i < $requestCount; $i++) {
            DB::table('api_requests')->insert([
                'serverid' => $serverIds[array_rand($serverIds)],
                'execution_time' => rand(100, 2000) / 100,
                'status' => rand(1, 10) > 1 ? 'success' : 'failed',
                'request_time' => $time->copy()->addDays(rand(0, 6))->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
            ]);
        }
    }

    private function generateRequestsForMonth(Carbon $time, array $serverIds): void
    {
        $requestCount = rand(5000, 10000);
        for ($i = 0; $i < $requestCount; $i++) {
            DB::table('api_requests')->insert([
                'serverid' => $serverIds[array_rand($serverIds)],
                'execution_time' => rand(100, 2000) / 100,
                'status' => rand(1, 10) > 1 ? 'success' : 'failed',
                'request_time' => $time->copy()->addDays(rand(0, 29))->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
            ]);
        }
    }
}