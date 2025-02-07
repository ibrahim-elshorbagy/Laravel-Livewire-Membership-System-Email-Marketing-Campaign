<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --queue=default,high --stop-when-empty  --timeout=7200 --tries=5', [])
    ->everyMinute()
    ->withoutOverlapping()
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
