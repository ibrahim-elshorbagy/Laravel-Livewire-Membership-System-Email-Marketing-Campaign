<?php

use App\Livewire\Pages\User\Emails\Campaign\CampaignForm;
use App\Livewire\Pages\User\Emails\Campaign\CampaignList;
use App\Livewire\Pages\User\Support\Support;
use App\Livewire\Pages\User\Emails\MessageForm;
use App\Livewire\Pages\User\Emails\MessageList;
use App\Livewire\Pages\User\Emails\CreateEmailList;
use App\Livewire\Pages\User\Emails\EmailListsTable;
use App\Livewire\Pages\User\Emails\Partials\ShowMessage;
use App\Livewire\Pages\User\Report\Email\EmailBounceReport;
use App\Livewire\Pages\User\Report\Email\EmailFilters;
use App\Livewire\Pages\User\Server\ServerList;
use App\Livewire\Pages\User\Subscription\MySubscription;
use App\Livewire\Pages\User\Subscription\Subscribe;
use App\Livewire\Pages\User\Subscription\Transaction;
use App\Livewire\Pages\User\Subscription\Transaction\TransactionInfo;
use App\Livewire\Pages\User\Support\TicketDetail;
use App\Livewire\Pages\User\Support\TicketList;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;





// WebHook Payments

Route::webhooks('/paypal/webhook', 'paypal');









    Route::get('/plans', Subscribe::class)->name('our.plans');


Route::middleware(['auth','role:user'])->group(function () {

    // Reports
    Route::get('/report/email-bounce', EmailBounceReport::class)->name('user.report.email-bounce');
    Route::get('/report/email-filters', EmailFilters::class)->name('user.report.email-filters');


    //Subscription
    Route::get('/my-subscription', MySubscription::class)->name('user.my-subscription');
    Route::get('/my-transactions', Transaction::class)->name('user.my-transactions');
    Route::get('/my-transactions/{payment}/info',TransactionInfo::class)->name('user.my-transaction.info');




    //Emails
    Route::get('/emails', EmailListsTable::class)->name('user.emails.index');
    Route::get('/emails/create', CreateEmailList::class)->name('user.emails.create');



    //Messages
    Route::get('/message-form/{message?}', MessageForm::class)->name('user.emails.message.form');
    Route::get('/email-messages', MessageList::class)->name('user.email-messages');
    Route::get('/email-messages/{message}/show', ShowMessage::class)->name('user.emails.message.show');

    //Servers
    Route::get('/my-bots', ServerList::class)->name('user.servers');

    //Campaigns
    Route::get('/campaigns', CampaignList::class)->name('user.campaigns.list');
    Route::get('/campaigns/form/{campaign?}', CampaignForm::class)->name('user.campaigns.form');
    Route::get('/campaigns/{campaign}/progress', App\Livewire\Pages\User\Emails\Campaign\Progress::class)
        ->name('user.campaigns.progress');


    //Support
    Route::get('/support/create-ticket', Support::class)->name('user.support.create');
    Route::get('/support/tickets', TicketList::class)->name('user.support.tickets');
    Route::get('/support/ticket/{ticket}', TicketDetail::class)->name('user.support.ticket');



});
