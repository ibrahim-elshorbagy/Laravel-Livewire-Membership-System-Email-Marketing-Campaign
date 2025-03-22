<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages\Admin\Payment\Offline\OfflinPaymenteMethods;
use App\Livewire\Pages\Admin\Payment\Offline\OfflinPaymenteMethodsForm;
use App\Livewire\Pages\Admin\Payment\Paypal\PaypalResponses;
use App\Livewire\Pages\Admin\Payment\PaypalConfig;
use App\Livewire\Pages\Admin\Plans\PlanManagement;
use App\Livewire\Pages\Admin\Plans\PlanManagement\Edit as PlanManagementEdit;
use App\Livewire\Pages\Admin\Server\ServerForm;
use App\Livewire\Pages\Admin\Server\ServerList;
use App\Livewire\Pages\Admin\SiteSettings\ApiErrors;
use App\Livewire\Pages\Admin\SiteSettings\ApiRequests;
use App\Livewire\Pages\Admin\SiteSettings\ProhibitedWords;
use App\Livewire\Pages\Admin\SiteSettings\SiteSettings;
use App\Livewire\Pages\Admin\Subscription\Subscripers;
use App\Livewire\Pages\Admin\Subscription\Subscripers\EditSubscription;
use App\Livewire\Pages\Admin\Support\TicketDetail;
use App\Livewire\Pages\Admin\Support\TicketManagement;
use App\Livewire\Pages\Admin\Transactions\EditPayment;
use App\Livewire\Pages\Admin\Transactions\Transactions;
use App\Livewire\Pages\Admin\Transactions\User\UserTransactions;
use App\Livewire\Pages\Admin\User\UserManagement;
use App\Livewire\Pages\Admin\User\UserManagement\Create;
use App\Livewire\Pages\Admin\User\UserManagement\Edit;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::middleware(['auth', 'impersonation.check'])->group(function () {
        Route::get('/revert-impersonate', [UserController::class, 'revertImpersonate'])
            ->name('revert.impersonate');
    });
    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

        // User Management
        Route::get('/users', UserManagement::class)->name('admin.users');
        Route::get('/users/create', Create::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', Edit::class)->name('admin.users.edit');

        // Subscription Management
        Route::get('/subscriptions', Subscripers::class)->name('admin.subscriptions');
        Route::get('/subscriptions/{subscription}/edit', EditSubscription::class)
            ->name('admin.subscriptions.edit')
            ->scopeBindings(false);


        // Plan Management
        Route::get('/plans', PlanManagement::class)->name('admin.plans');
        Route::get('/plans/{plan}/edit', PlanManagementEdit::class)->name('admin.plans.edit');


        // Site Settings
        Route::get('/site/site-settings', SiteSettings::class)->name('admin.site-settings');
        Route::get('/site/api-errors', ApiErrors::class)->name('admin.site-api-errors');
        Route::get('/site/api-requests', ApiRequests::class)->name('admin.site-api-requests');
        Route::get('/site/prohibited-words', ProhibitedWords::class)->name('admin.site-prohibited-words');


        // Payment Settings
        Route::get('/payment/paypal', PaypalConfig::class)->name('admin.payment.paypal');
        Route::get('/payment/paypal-responses', PaypalResponses::class)->name('admin.payment.paypal.responses');

        Route::get('/payment/offline', OfflinPaymenteMethods::class)->name('admin.offline-payment-methods');
        Route::get('/payment/offline-form/{method?}', OfflinPaymenteMethodsForm::class)->name('admin.offline-payment-methods.form');



        Route::get('/transactions', Transactions::class)->name('admin.payment.transactions');
        Route::get('/transactions/{payment}/edit',EditPayment::class)->name('admin.transactions.edit');
        Route::get('/transactions/{user}/transaction', UserTransactions::class)->name('admin.users.transactions');


        // Servers
        Route::get('/servers', ServerList::class)->name('admin.servers');
        Route::get('/servers/form/{server?}', ServerForm::class)->name('admin.servers.form');

        // Support Ticket Management
        Route::get('/support/tickets', TicketManagement::class)->name('admin.support.tickets');
        Route::get('/support/ticket/{ticket}', TicketDetail::class)->name('admin.support.ticket-detail');

    });

