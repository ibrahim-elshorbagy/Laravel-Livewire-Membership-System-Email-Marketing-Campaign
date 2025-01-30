<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();


Schedule::command('queue:listen --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping(60)
    ->onOneServer()
    ->runInBackground();
