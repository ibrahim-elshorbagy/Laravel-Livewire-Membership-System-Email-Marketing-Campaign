<?php

namespace App\Livewire\Pages\Admin\SiteSettings;

use App\Http\Middleware\GlobalSettingsMiddleware;
use App\Models\Admin\Site\SiteSetting;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class SiteSettings extends Component
{
    use WithFileUploads, LivewireAlert;

    public $site_name;
    public $support_email;
    public $support_phone;
    public $logo;
    public $favicon;
    public $new_logo;
    public $new_favicon;

    // New properties for image previews
    public $logo_preview;
    public $favicon_preview;

    // Meta properties
    public $meta_title;
    public $meta_description;
    public $meta_keywords;

    // Footer properties
    public $footer_first_line;
    public $footer_second_line;

    protected $rules = [
        'site_name' => 'required|string|max:255',
        'support_email' => 'required|email',
        'support_phone' => 'required|string|max:20',
        'new_logo' => 'nullable|image|max:2048',
        'new_favicon' => 'nullable|image|max:1024',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:500',
        'meta_keywords' => 'nullable|string|max:255',

        'footer_first_line' => 'nullable|string|max:255',
        'footer_second_line' => 'nullable|string|max:255',

    ];

    public function mount()
    {
        $this->site_name = config('app.name');
        $this->support_email = SiteSetting::getValue('support_email');
        $this->support_phone = SiteSetting::getValue('support_phone');
        $this->logo = SiteSetting::getValue('logo');
        $this->favicon = SiteSetting::getValue('favicon');
        $this->meta_title = SiteSetting::getValue('meta_title');
        $this->meta_description = SiteSetting::getValue('meta_description');
        $this->meta_keywords = SiteSetting::getValue('meta_keywords');

        $this->footer_first_line = SiteSetting::getValue('footer_first_line');
        $this->footer_second_line = SiteSetting::getValue('footer_second_line');

    }

    // Preview logo
    public function updatedNewLogo()
    {
        $this->validate([
            'new_logo' => 'image|max:2048'
        ]);

        // Create a temporary preview
        $this->logo_preview = $this->new_logo->temporaryUrl();
    }

    // Preview favicon
    public function updatedNewFavicon()
    {
        $this->validate([
            'new_favicon' => 'image|max:1024'
        ]);

        // Create a temporary preview
        $this->favicon_preview = $this->new_favicon->temporaryUrl();
    }

    public function updateSiteSettings()
    {
        $this->validate();

        try {
            // Prepare settings with environment and config mappings
            $settings = [
                'site_name' => [
                    'value' => $this->site_name,
                    'env_keys' => [
                        'APP_NAME' => 'app.name',
                        'SITE_NAME' => 'app.site_name'
                    ]
                ],
                'support_email' => [
                    'value' => $this->support_email,
                    'env_keys' => [
                        'SUPPORT_EMAIL' => 'mail.support_email'
                    ]
                ],
                'support_phone' => [
                    'value' => $this->support_phone,
                    'env_keys' => [
                        'SUPPORT_PHONE' => 'app.support_phone'
                    ]
                ]
            ];

            // Update text settings with environment and config updates
            foreach ($settings as $property => $setting) {
                $this->updateSettingWithEnvironment($property, $setting['value'], $setting['env_keys']);
            }

            SiteSetting::setValue('meta_description', $this->meta_description);
            SiteSetting::setValue('meta_keywords', $this->meta_keywords);

            // Update footer settings
            SiteSetting::setValue('footer_first_line', $this->footer_first_line);
            SiteSetting::setValue('footer_second_line', $this->footer_second_line);

            // Handle Logo Upload
            if ($this->new_logo) {
                // Delete old logo if exists
                if ($this->logo) {
                    Storage::disk('public')->delete($this->logo);
                }

                // Store new logo
                $logoPath = $this->new_logo->store('site/logos', 'public');
                $this->updateSettingWithEnvironment('logo', $logoPath, [
                    'SITE_LOGO' => 'app.logo'
                ]);
                $this->logo = $logoPath;
                $this->logo_preview = null; // Clear preview
            }

            // Handle Favicon Upload
            if ($this->new_favicon) {
                // Delete old favicon if exists
                if ($this->favicon) {
                    Storage::disk('public')->delete($this->favicon);
                }

                // Store new favicon
                $faviconPath = $this->new_favicon->store('site/favicons', 'public');
                $this->updateSettingWithEnvironment('favicon', $faviconPath, [
                    'SITE_FAVICON' => 'app.favicon'
                ]);
                $this->favicon = $faviconPath;
                $this->favicon_preview = null; // Clear preview
            }


            // Clear the global settings cache
            GlobalSettingsMiddleware::clearCache();

            // Clear config cache
            Artisan::call('config:clear');

            // Clear view cache (optional, but recommended when updating site settings)
            Artisan::call('view:clear');


            Session::flash('success', 'Site settings updated successfully.');
            return $this->redirect(route('admin.site-settings'), navigate: true);


        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update site settings: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    /**
     * Update setting with environment and config modifications
     *
     * @param string $property
     * @param mixed $value
     * @param array $envKeys
     */
    protected function updateSettingWithEnvironment(string $property, $value, array $envKeys = [])
    {
        // Update database setting
        SiteSetting::updateOrCreate(
            ['property' => $property],
            ['value' => $value]
        );

        // Update .env and config files
        $this->updateEnvironmentFile($envKeys, $value);
    }

    /**
     * Update the .env file with new values
     *
     * @param array $values
     * @param mixed $value
     * @return void
     */
    private function updateEnvironmentFile(array $values, $value)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        foreach ($values as $key => $configPath) {
            $escapedValue = str_replace(['\\', '"'], ['\\\\', '\"'], $value);

            // Check if the key exists in the .env file
            if (preg_match("/^{$key}=.*$/m", $str)) {
                // Replace existing key
                $str = preg_replace("/^{$key}=.*$/m", "{$key}=\"{$escapedValue}\"", $str);
            } else {
                // Add new key if it doesn't exist
                $str .= "\n{$key}=\"{$escapedValue}\"";
            }
        }

        file_put_contents($envFile, $str);

        // Clear config cache
        Artisan::call('config:clear');
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.site-settings')
            ->layout('layouts.app',['title' => 'Site Settings']);
    }
}
