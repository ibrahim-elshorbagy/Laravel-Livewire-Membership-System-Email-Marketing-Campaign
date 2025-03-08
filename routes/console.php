<?php

use App\Models\JobProgress;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;


Schedule::call(function () {
    Log::info("Cron Works at test_6");
});

Schedule::command('queue:work --queue=default,high --tries=5  --max-time=86400', [])
    ->withoutOverlapping(86400)
    ->before(function () {
        Log::info('Starting default and high queue worker');
    })
    ->after(function () {
        Log::info('Queue worker completed successfully');
    })
    ->onFailure(function () {
        Log::error('Queue worker failed to complete');
    })
    ->then(function () {
        Log::info('Closed default and high queue worker');
    });


Schedule::call(function () {
    JobProgress::where('status', 'completed')->delete();
});

