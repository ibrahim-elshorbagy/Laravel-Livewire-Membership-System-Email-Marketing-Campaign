<?php

use App\Livewire\Pages\User\Support\Support;
use App\Livewire\Pages\User\Emails\CampaignForm;
use App\Livewire\Pages\User\Emails\CampaignList;
use App\Livewire\Pages\User\Emails\CreateEmailList;
use App\Livewire\Pages\User\Emails\EmailListsTable;
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

    Route::get('/campaign-form/{campaign?}', CampaignForm::class)->name('user.emails.campaign.form');
    Route::get('/email-campaigns', CampaignList::class)->name('user.email-campaigns');
    Route::get('/emails', EmailListsTable::class)->name('user.emails.index');
    Route::get('/emails/create', CreateEmailList::class)->name('user.emails.create');

    Route::get('/support', Support::class)->name('user.support');


});

