<?php

use App\Models\JobProgress;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;


Schedule::call(function () {
    Log::info("Cron Works at test_7");
});

Schedule::command('queue:work --queue=default,high --tries=5 --stop-when-empty', [])
    ->everyTenSeconds()
    ->withoutOverlapping()
    ->before(function () {
        Log::info('Starting queue:work...');
    })
    ->after(function () {
        Log::info('Queue worker completed successfully.');
    })
    ->onFailure(function () {
        Log::error('Queue worker failed.');
    })
    ->then(function () {
        Log::info('Closed queue worker.');
    });



Schedule::call(function () {
    JobProgress::where('status', 'completed')->delete();
});

