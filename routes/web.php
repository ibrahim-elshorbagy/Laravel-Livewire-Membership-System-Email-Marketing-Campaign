<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('play-ground', 'play-ground')->name('play-ground')
    ->middleware(['auth']);






require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
