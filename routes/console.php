<?php

use App\Models\JobProgress;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\Notification\SubscriptionNotifier;



Schedule::call(function () {
    Log::channel('worker')->info('Cron Works');
});

Schedule::call(function () {
    (new SubscriptionNotifier())->SubscriptionAboutToEndNotify();
})
    // ->daily()
    ->before(function () {
        Log::channel('worker')->info('SubscriptionNotifier work...');
    })
    ->after(function () {
        Log::channel('worker')->info('SubscriptionNotifier completed successfully....');
    })
    ->onFailure(function () {
        Log::channel('worker')->error('SubscriptionNotifier failed...');
    })
    ->then(function () {
        Log::channel('worker')->info('SubscriptionNotifier Closed...');
    });


Schedule::call(function () {
    JobProgress::where('status', 'completed')->delete();
});


Schedule::command('queue:work --queue=default,high --tries=5 --stop-when-empty', [])
    ->everyTenSeconds()
    ->withoutOverlapping()
    ->before(function () {
        // Log::channel('worker')->info('Starting queue:work...');
    })
    ->after(function () {
        // Log::channel('worker')->info('Queue worker completed successfully.');
    })
    ->onFailure(function () {
        // Log::channel('worker')->error('Queue worker failed.');
    })
    ->then(function () {
        // Log::channel('worker')->info('Closed queue worker.');
    });


