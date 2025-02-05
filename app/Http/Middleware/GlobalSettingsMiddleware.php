<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin\Site\SiteSetting;
use Illuminate\Support\Facades\View;

class GlobalSettingsMiddleware
{
/**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fetch global settings
        $globalSettings = $this->getGlobalSettings();

        // Share settings with all views
        View::share('globalSettings', $globalSettings);


        // If it's a Livewire request, you can use a custom method
        if (class_exists('\Livewire\Livewire')) {
            $this->shareLivewireSettings($globalSettings);
        }

        return $next($request);
    }

    /**
     * Retrieve global settings with caching
     *
     * @return array
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

            // Add more settings as needed...

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
}
