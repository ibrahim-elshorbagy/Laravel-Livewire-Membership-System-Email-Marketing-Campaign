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





Route::get('/payment/close', function () {
    return view('payment.close');
})->name('payment.close');

// Route::fallback(function () {
//     return response()->view('errors.404', [], 404);
// });
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/user.php';



