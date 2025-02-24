<?php

use App\Http\Controllers\Api\EmailGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/gateway/get-details', [EmailGatewayController::class, 'getDetails'])
    ->name('email.gateway');
