<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages\Admin\User\UserManagement;
use App\Livewire\Pages\Admin\User\UserManagement\Create;
use App\Livewire\Pages\Admin\User\UserManagement\Edit;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

        Route::get('/users', UserManagement::class)->name('admin.users');
        Route::get('/users/create', Create::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', Edit::class)->name('admin.users.edit');

    });
