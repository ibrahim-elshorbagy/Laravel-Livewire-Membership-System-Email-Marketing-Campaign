<?php

use App\Models\JobProgress;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

Schedule::command('queue:work --queue=default,high  --tries=5')
    ->everyFifteenMinutes()
    ->runInBackground()
    ->withoutOverlapping(900) // 15 minutes
    ->before(function () {
        Log::info('Queue worker starting');
    })
    ->after(function () {
        Log::info('Queue worker finished');
    })
    ->onFailure(function (\Throwable $e) {
        Log::error('Queue worker failed: '.$e->getMessage());
    });

Schedule::call(function () {
    JobProgress::where('status', 'completed')->delete();
});
