<?php

namespace App\Livewire\Pages\Admin\SiteSettings\Settings;

use App\Models\Admin\Site\SiteSetting;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use OpenAI;

class AiSettings extends Component
{
    use LivewireAlert;

    public $openai_active;
    public $openai_api_key;
    public $openai_model;
    public $openai_organization;
    public $openai_project;
    public $openai_role;
    public $prompt;

    protected $rules = [
        'openai_active' => 'nullable|boolean',
        'openai_api_key' => 'nullable|string|max:255',
        'openai_model' => 'nullable|string|max:100',
        'openai_organization' => 'nullable|string|max:255',
        'openai_project' => 'nullable|string|max:255',
        'openai_role' => 'nullable|string|max:50',
        'prompt' => 'nullable|string|max:2000',
    ];

    public function mount()
    {
        // Load AI settings from database and environment
        $this->openai_active = SiteSetting::getValue('openai_active', config('services.openai.active', false));
        $this->openai_api_key = SiteSetting::getValue('openai_api_key', config('services.openai.api_key'));
        $this->openai_model = SiteSetting::getValue('openai_model', config('services.openai.model', 'gpt-4o'));
        $this->openai_organization = SiteSetting::getValue('openai_organization', config('services.openai.organization'));
        $this->openai_project = SiteSetting::getValue('openai_project', config('services.openai.project'));
        $this->openai_role = SiteSetting::getValue('openai_role', config('services.openai.role', 'user'));
        $this->prompt = SiteSetting::getValue('prompt', 'Generate a html email template with the following conditions');
    }

    public function updateAiSettings()
    {
        $this->validate();

        try {
            // Convert checkbox value to proper boolean
            $this->openai_active = (bool) $this->openai_active;

            // Prepare settings with environment and config mappings
            $settings = [
                'openai_active' => [
                    'value' => $this->openai_active,
                    'env_keys' => [
                        'OPENAI_ACTIVE' => 'services.openai.active'
                    ]
                ],
                'openai_api_key' => [
                    'value' => $this->openai_api_key,
                    'env_keys' => [
                        'OPENAI_API_KEY' => 'services.openai.api_key'
                    ]
                ],
                'openai_model' => [
                    'value' => $this->openai_model,
                    'env_keys' => [
                        'OPENAI_MODEL' => 'services.openai.model'
                    ]
                ],
                'openai_organization' => [
                    'value' => $this->openai_organization,
                    'env_keys' => [
                        'OPENAI_ORGANIZATION' => 'services.openai.organization'
                    ]
                ],
                'openai_project' => [
                    'value' => $this->openai_project,
                    'env_keys' => [
                        'OPENAI_PROJECT' => 'services.openai.project'
                    ]
                ],
                'openai_role' => [
                    'value' => $this->openai_role,
                    'env_keys' => [
                        'OPENAI_ROLE' => 'services.openai.role'
                    ]
                ],
                'prompt' => [
                    'value' => $this->prompt,
                    'env_keys' => []
                ]
            ];

            // Update each setting
            foreach ($settings as $property => $config) {
                $this->updateSettingWithEnvironment($property, $config['value'], $config['env_keys']);
            }

            $this->alert('success', 'AI Settings updated successfully!');

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update AI settings: ' . $e->getMessage());
        }
    }

    /**
     * Update setting with environment file synchronization
     */
    protected function updateSettingWithEnvironment(string $property, $value, array $envKeys = [])
    {
        // Store in database
        SiteSetting::setValue($property, $value);

        // Update environment file if env keys are provided
        if (!empty($envKeys)) {
            $this->updateEnvironmentFile($envKeys, $value);
        }
    }

    /**
     * Update environment file with new values
     */
    private function updateEnvironmentFile(array $values, $value)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($values as $envKey => $configPath) {
            $pattern = "/^{$envKey}=.*/m";
            $replacement = "{$envKey}=" . (is_bool($value) ? ($value ? 'true' : 'false') : '"' . $value . '"');

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.settings.ai-settings')->layout('layouts.app',['title' => 'Ai Settings']);
    }
}
