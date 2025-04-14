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
use App\Models\Admin\Site\SystemSetting\BouncePattern;
use Livewire\WithPagination;

class SiteSettings extends Component
{
    use WithFileUploads, LivewireAlert, WithPagination;

    // Bounce Pattern properties
    public $newPatternType = '';
    public $newPattern = '';
    public $filterType = '';
    public $perPage = 10;


    public $site_name;
    public $support_email;
    public $support_phone;
    public $logo;
    public $favicon;
    public $new_logo;
    public $new_favicon;

    // Mail settings
    public $mail_mailer;
    public $mail_host;
    public $mail_port;
    public $mail_username;
    public $mail_password;
    public $mail_from_address;
    public $mail_from_name;

    //Settings
    public $APP_TIMEZONE;
    public $maintenance;
    public $our_devices;
    public $grace_days;

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

    public $auth_image;
    public $new_auth_image;
    public $auth_image_preview;


    protected $rules = [
        'site_name' => 'required|string|max:255',
        'support_email' => 'required|email',
        'support_phone' => 'required|string|max:20',
        'new_logo' => 'nullable|image',
        'new_favicon' => 'nullable|image',
        'new_auth_image' => 'nullable|image',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:500',
        'meta_keywords' => 'nullable|string|max:255',
        'APP_TIMEZONE' => 'required|string',
        'footer_first_line' => 'nullable|string|max:255',
        'footer_second_line' => 'nullable|string|max:255',
        'maintenance' => 'nullable|boolean',
        'our_devices' => 'nullable|boolean',
        'mail_mailer' => 'required|string',
        'mail_host' => 'required|string',
        'mail_port' => 'required|numeric',
        'mail_username' => 'required|string',
        'mail_password' => 'required|string',
        'mail_from_address' => 'required|email',
        'mail_from_name' => 'required|string',
        'grace_days' => 'required|integer|min:0',
    ];


    public function mount()
    {
        // Load bounce patterns with pagination and filtering
        $this->site_name = config('app.name');

        // Load mail settings
        $this->mail_mailer = config('mail.default');
        $this->mail_host = config('mail.mailers.smtp.host');
        $this->mail_port = config('mail.mailers.smtp.port');
        $this->mail_username = config('mail.mailers.smtp.username');
        $this->mail_password = config('mail.mailers.smtp.password');
        $this->mail_from_address = config('mail.from.address');
        $this->mail_from_name = config('mail.from.name');
        $this->support_email = SiteSetting::getValue('support_email');
        $this->support_phone = SiteSetting::getValue('support_phone');
        $this->logo = SiteSetting::getValue('logo');
        $this->favicon = SiteSetting::getValue('favicon');
        $this->auth_image = SiteSetting::getValue('auth_image');
        $this->meta_title = SiteSetting::getValue('meta_title');
        $this->meta_description = SiteSetting::getValue('meta_description');
        $this->meta_keywords = SiteSetting::getValue('meta_keywords');
        $this->APP_TIMEZONE = SiteSetting::getValue('APP_TIMEZONE');
        $this->footer_first_line = SiteSetting::getValue('footer_first_line');
        $this->footer_second_line = SiteSetting::getValue('footer_second_line');
        $this->maintenance = SiteSetting::getValue('maintenance');
        $this->our_devices = SiteSetting::getValue('our_devices');
        $this->grace_days = SiteSetting::getValue('grace_days') ?? 0;
    }

    // Preview auth image
    public function updatedNewAuthImage()
    {
        $this->validate([
            'new_auth_image' => 'image|max:2048'
        ]);

        // Create a temporary preview
        $this->auth_image_preview = $this->new_auth_image->temporaryUrl();
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
            'new_favicon' => 'image'
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
                ],
                'APP_TIMEZONE' => [
                    'value' => $this->APP_TIMEZONE,
                    'env_keys' => [
                        'APP_TIMEZONE' => 'app.timezone'
                    ]
                ],
                'mail_mailer' => [
                    'value' => $this->mail_mailer,
                    'env_keys' => [
                        'MAIL_MAILER' => 'mail.default'
                    ]
                ],
                'mail_host' => [
                    'value' => $this->mail_host,
                    'env_keys' => [
                        'MAIL_HOST' => 'mail.mailers.smtp.host'
                    ]
                ],
                'mail_port' => [
                    'value' => $this->mail_port,
                    'env_keys' => [
                        'MAIL_PORT' => 'mail.mailers.smtp.port'
                    ]
                ],
                'mail_username' => [
                    'value' => $this->mail_username,
                    'env_keys' => [
                        'MAIL_USERNAME' => 'mail.mailers.smtp.username'
                    ]
                ],
                'mail_password' => [
                    'value' => $this->mail_password,
                    'env_keys' => [
                        'MAIL_PASSWORD' => 'mail.mailers.smtp.password'
                    ]
                ],
                'mail_from_name' => [
                    'value' => $this->mail_from_name,
                    'env_keys' => [
                        'MAIL_FROM_NAME' => 'mail.from.name'
                    ]
                ],
                'mail_from_address' => [
                                    'value' => $this->mail_from_address,
                                    'env_keys' => [
                                        'MAIL_FROM_ADDRESS' => 'mail_from_address'
                                    ]
                                ]
            ];

            // Update text settings with environment and config updates
            foreach ($settings as $property => $setting) {
                $this->updateSettingWithEnvironment($property, $setting['value'], $setting['env_keys']);
            }

            SiteSetting::setValue('meta_description', $this->meta_description);
            SiteSetting::setValue('meta_keywords', $this->meta_keywords);

            // Settings
            SiteSetting::setValue('APP_TIMEZONE', $this->APP_TIMEZONE);
            SiteSetting::setValue('maintenance', $this->maintenance);
            SiteSetting::setValue('our_devices', $this->our_devices);

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


            // Handle Auth Image Upload
            if ($this->new_auth_image) {
                // Delete old auth image if exists
                if ($this->auth_image) {
                    Storage::disk('public')->delete($this->auth_image);
                }

                // Store new auth image
                $authImagePath = $this->new_auth_image->store('site/auth', 'public');
                $this->updateSettingWithEnvironment('auth_image', $authImagePath, [
                    'AUTH_IMAGE' => 'app.auth_image'
                ]);
                $this->auth_image = $authImagePath;
                $this->auth_image_preview = null; // Clear preview
            }

            // Update grace days setting
            SiteSetting::setValue('grace_days', $this->grace_days);

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

    public $selectedPatternId = '';
    public $editPatternType = '';
    public $editPattern = '';

    public function addBouncePattern()
    {
        $this->validate([
            'newPatternType' => 'required|in:subject,hard,soft',
            'newPattern' => 'required|string'
        ]);

        $patterns = Str::of($this->newPattern)
            ->explode("\n")
            ->map(fn($pattern) => trim($pattern))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        foreach ($patterns as $pattern) {
            BouncePattern::create([
                'type' => $this->newPatternType,
                'pattern' => $pattern,
            ]);
        }

        $this->bouncePatterns = BouncePattern::all();
        $this->newPatternType = '';
        $this->newPattern = '';
        $this->alert('success', count($patterns) . ' bounce pattern(s) added successfully.', ['position' => 'bottom-end']);
    }

    public function updatePattern()
    {
        $this->validate([
            'editPatternType' => 'required|in:subject,hard,soft',
            'editPattern' => 'required|string|max:255'
        ]);

        $pattern = BouncePattern::find($this->selectedPatternId);
        if ($pattern) {
            $pattern->update([
                'type' => $this->editPatternType,
                'pattern' => $this->editPattern
            ]);

            $this->bouncePatterns = BouncePattern::all();
            $this->selectedPatternId = '';
            $this->editPatternType = '';
            $this->editPattern = '';
            $this->alert('success', 'Pattern updated successfully.', ['position' => 'bottom-end']);
            $this->dispatch('close-modal', 'edit-pattern-modal');
        }
    }

    public function deletePattern($id)
    {
        $pattern = BouncePattern::find($id);
        if ($pattern) {
            $pattern->delete();
            $this->bouncePatterns = BouncePattern::all();
            $this->alert('success', 'Pattern deleted successfully.', ['position' => 'bottom-end']);
        }
    }

    public function loadBouncePatterns()
    {
        $query = BouncePattern::query();

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedFilterType()
    {
        $this->bouncePatterns = $this->loadBouncePatterns();
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.site-settings', [
            'bouncePatterns' => $this->loadBouncePatterns()
        ])->layout('layouts.app',['title' => 'Site Settings']);
    }

}
