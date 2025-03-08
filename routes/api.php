<?php

use App\Http\Controllers\Api\CronController;
use App\Http\Controllers\Api\EmailGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/gateway/get-details', [EmailGatewayController::class, 'getDetails'])
    ->name('email.gateway');

Route::get('/settings/cron', [CronController::class, 'cronJob'])
    ->name('settings.cron');
