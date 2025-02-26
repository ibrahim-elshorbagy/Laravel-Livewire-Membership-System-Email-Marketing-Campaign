<?php

namespace App\Providers;

use App\Models\Campaign\CampaignEmailList;
use App\Models\Campaign\CampaignServer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

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

        Model::unguard();


    Validator::extend('without_space', function($attr, $value, $parameters, $validator) {
        // Remove all spaces from the value
        $processedValue = str_replace(' ', '', $value);

        // Update the value in the validator
        $validator->setData(array_merge(
            $validator->getData(),
            [$attr => $processedValue]
        ));

        // Check if the value contains only English letters, dots, and hyphens
        if (!preg_match('/^[a-zA-Z.-]+$/', $processedValue)) {
            $validator->setCustomMessages([
                'without_space' => 'The :attribute must contain only English letters, . , and -'
            ]);
            return false;
        }

        return true;
    });

    }
}
