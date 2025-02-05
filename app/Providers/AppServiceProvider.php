<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('subscription', function ($value) {
            return Subscription::withoutGlobalScopes([SuppressingScope::class, StartingScope::class])
                ->with(['plan', 'subscriber' => function ($query) {
                    $query->withTrashed();
                }])
                ->findOrFail($value);
        });


    }
}
