<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages\Admin\Payment\PaypalConfig;
use App\Livewire\Pages\Admin\Plans\PlanManagement;
use App\Livewire\Pages\Admin\Plans\PlanManagement\Edit as PlanManagementEdit;
use App\Livewire\Pages\Admin\SiteSettings\SiteSettings;
use App\Livewire\Pages\Admin\Subscription\Subscripers;
use App\Livewire\Pages\Admin\Subscription\Subscripers\EditSubscription;
use App\Livewire\Pages\Admin\User\UserManagement;
use App\Livewire\Pages\Admin\User\UserManagement\Create;
use App\Livewire\Pages\Admin\User\UserManagement\Edit;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

        // User Management
        Route::get('/users', UserManagement::class)->name('admin.users');
        Route::get('/users/create', Create::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', Edit::class)->name('admin.users.edit');

        // Subscription Management
        Route::get('/subscriptions', Subscripers::class)->name('admin.subscriptions');
        Route::get('/subscriptions/{subscription:id}/edit', EditSubscription::class)->name('admin.subscriptions.edit');


        // Plan Management
        Route::get('/plans', PlanManagement::class)->name('admin.plans');
        Route::get('/plans/{plan}/edit', PlanManagementEdit::class)->name('admin.plans.edit');


        // Site Settings
        Route::get('/site/site-settings', SiteSettings::class)->name('admin.site-settings');


        // Payment Settings
        Route::get('/payment/paypal', PaypalConfig::class)->name('admin.payment.paypal');

    });

