<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

// Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
//         ->middleware(['signed', 'throttle:6,1'])
//         ->name('verification.verify');

Route::get('verify-email-unauthenticated/{id}/{hash}', [VerifyEmailController::class, 'Verify'])
        // ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify.unauthenticated');

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');


    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    // Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});
