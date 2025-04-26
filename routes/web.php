<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('welcome');
Route::redirect('/main-site', 'https://bulkemailapp.com/')->name('main-site');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('profile/settings', 'livewire.pages.user.dashboard.settings')
    ->middleware(['auth','role:user'])
    ->name('user-settings');



Route::get('/payment/close', function () {
    return view('payment.close');
})->name('payment.close');

// Route::fallback(function () {
//     return response()->view('errors.404', [], 404);
// });
// Secure chat image route
Route::get('/chat/images/{userId}/{filename}', [\App\Http\Controllers\ChatImageController::class, 'show'])
    ->middleware(['auth'])
    ->name('chat.image');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/user.php';



