<?php

namespace App\Handler;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PaypalSignature implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        try {
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $headers = $request->headers->all();
            $body = $request->getContent();

            // Log headers and body for debugging
            // Log::debug('Paypal Signature Webhook Headers', ['headers' => $headers]);
            // Log::debug('Paypal Signature Webhook Body', ['body' => json_decode($body, true)]);

            $verificationData = [
                'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url'          => $request->header('PAYPAL-CERT-URL'),
                'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id'        => config('paypal.webhook_id'),
                'webhook_event'     => json_decode($body, true),
            ];

            // Log verification data
            // Log::debug('Paypal Signature Verification Data', $verificationData);

            $response = $provider->verifyWebHook($verificationData);

            // Log::debug('Paypal Signature Verification Response', ['response' => $response]);

            return ($response['verification_status'] ?? '') === 'SUCCESS';

        } catch (\Exception $e) {
            Log::error('PayPal Signature Validation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
