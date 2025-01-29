<?php

namespace App\Livewire\Pages\Admin\Payment;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class PaypalConfig extends Component
{
    use LivewireAlert;

    public $mode = 'sandbox';
    public $client_id;
    public $client_secret;
    public $app_id;
    public $webhook_id;
    public $after_success_payment_url;
    public $after_cancel_payment_url;

    protected $rules = [
        'mode' => 'required|in:sandbox,live',
        'client_id' => 'required|string',
        'client_secret' => 'required|string',
        'app_id' => 'required|string',
        'webhook_id' => 'required|string',
    ];

    public function mount()
    {
        $this->mode = config('paypal.mode');
        $this->client_id = config('paypal.live.client_id');
        $this->client_secret = config('paypal.live.client_secret');
        $this->app_id = config('paypal.live.app_id');
        $this->webhook_id = config('paypal.webhook_id');
    }

    public function updatePaypalConfig()
    {
        $this->validate();

        try {
            $this->setEnvironmentValues([
                'PAYPAL_MODE' => $this->mode,
                'PAYPAL_LIVE_CLIENT_ID' => $this->client_id,
                'PAYPAL_LIVE_CLIENT_SECRET' => $this->client_secret,
                'PAYPAL_LIVE_APP_ID' => $this->app_id,
                'PAYPAL_SANDBOX_CLIENT_ID' => $this->client_id,
                'PAYPAL_SANDBOX_CLIENT_SECRET' => $this->client_secret,
                'PAYPAL_WEBHOOK_ID' => $this->webhook_id,
            ]);

            Artisan::call('config:clear');

            $this->alert('success', 'PayPal configuration updated successfully.');
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update PayPal configuration.');
        }
    }

    private function setEnvironmentValues(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            $escapedValue = str_replace(['\\', '"'], ['\\\\', '\"'], $value);

            if (preg_match("/^{$key}=.*$/m", $str)) {
                $str = preg_replace("/^{$key}=.*$/m", "{$key}=\"{$escapedValue}\"", $str);
            } else {
                $str .= "\n{$key}=\"{$escapedValue}\"";
            }
        }

        file_put_contents($envFile, $str);
    }

    public function render()
    {
        return view('livewire.pages.admin.payment.paypal-config')->layout('layouts.app');
    }
}
