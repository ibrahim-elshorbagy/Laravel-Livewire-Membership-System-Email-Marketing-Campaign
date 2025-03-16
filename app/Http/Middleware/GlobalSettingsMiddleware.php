<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin\Site\SiteSetting;
use Illuminate\Support\Facades\View;

class GlobalSettingsMiddleware
{
    private const CACHE_KEY = 'global_settings';
    private const CACHE_DURATION = 3600; // 1 hour in seconds

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fetch global settings from cache or database
        $globalSettings = $this->getCachedGlobalSettings();

        // Share settings with all views
        View::share('globalSettings', $globalSettings);

        // If it's a Livewire request, share settings
        if (class_exists('\Livewire\Livewire')) {
            $this->shareLivewireSettings($globalSettings);
        }

        return $next($request);
    }

    /**
     * Get cached global settings
     */
    protected function getCachedGlobalSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return $this->getGlobalSettings();
        });
    }

    /**
     * Retrieve global settings
     */
    protected function getGlobalSettings(): array
    {
        return [
            'site_name' => SiteSetting::getValue('site_name', config('app.name')),
            'support_email' => SiteSetting::getValue('support_email'),
            'support_phone' => SiteSetting::getValue('support_phone'),
            'logo' => SiteSetting::getLogo(),
            'favicon' => SiteSetting::getFavicon(),

            'meta_description' => SiteSetting::getValue('meta_description'),
            'meta_keywords' => SiteSetting::getValue('meta_keywords'),

            'APP_TIMEZONE' => SiteSetting::getValue('APP_TIMEZONE'),


            'footer_first_line' => SiteSetting::getValue('footer_first_line'),
            'footer_second_line' => SiteSetting::getValue('footer_second_line'),
        ];
    }

    /**
     * Share settings with Livewire components
     *
     * @param array $settings
     */
    protected function shareLivewireSettings(array $settings)
    {
        // Method to share settings with Livewire
        \Livewire\Livewire::listen('component.boot', function ($component) use ($settings) {
            foreach ($settings as $key => $value) {
                $component->set($key, $value);
            }
        });
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
