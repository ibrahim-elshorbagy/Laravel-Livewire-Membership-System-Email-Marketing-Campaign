<?php

use App\Livewire\Pages\User\Support\Support;
use App\Livewire\Pages\User\Emails\MessageForm;
use App\Livewire\Pages\User\Emails\MessageList;
use App\Livewire\Pages\User\Emails\CreateEmailList;
use App\Livewire\Pages\User\Emails\EmailListsTable;
use App\Livewire\Pages\User\Emails\Partials\ShowMessage;
use App\Livewire\Pages\User\Server\ServerList;
use App\Livewire\Pages\User\Subscription\MySubscription;
use App\Livewire\Pages\User\Subscription\Subscribe;
use App\Livewire\Pages\User\Subscription\Transaction;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::webhooks('/paypal/webhook', 'paypal');

Route::middleware(['auth'])->group(function () {

    Route::get('/plans', Subscribe::class)->name('our.plans');

});

Route::middleware(['auth','role:user'])->group(function () {


    Route::get('/my-subscription', MySubscription::class)->name('user.my-subscription');
    Route::get('/my-transactions', Transaction::class)->name('user.my-transactions');

    Route::get('/message-form/{message?}', MessageForm::class)->name('user.emails.message.form');
    Route::get('/email-messages', MessageList::class)->name('user.email-messages');
    Route::get('/emails', EmailListsTable::class)->name('user.emails.index');
    Route::get('/emails/create', CreateEmailList::class)->name('user.emails.create');
    Route::get('/email-messages/{message}/show', ShowMessage::class)->name('user.emails.message.show');


    Route::get('/support', Support::class)->name('user.support');

    Route::get('/my-servers', ServerList::class)->name('user.servers');

});
