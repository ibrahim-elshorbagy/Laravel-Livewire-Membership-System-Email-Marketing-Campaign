<?php

use App\Livewire\Pages\User\Subscription\MySubscription;
use App\Livewire\Pages\User\Subscription\Subscribe;
use App\Livewire\Pages\User\Subscription\Transaction;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::webhooks('/paypal/webhook', 'paypal');

Route::middleware(['auth'])->group(function () {

    Route::get('/plans', Subscribe::class)->name('our.plans');
    
    Route::get('/my-subscription', MySubscription::class)->name('user.my-subscription');
    Route::get('/my-transactions', Transaction::class)->name('user.my-transactions');

});

