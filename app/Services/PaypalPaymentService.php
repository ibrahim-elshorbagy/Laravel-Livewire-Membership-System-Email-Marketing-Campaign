<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

trait PaypalPaymentService
{
    protected $paypal;

    /**
     * Initialize PayPal Client with configuration and obtain access token.
     *
     * @return PayPalClient
     * @throws \Exception
     */
    public function initializePayPal()
    {

        // Validate PayPal configuration
        $paypalConfig = config('paypal');
        $this->paypal = new PayPalClient();
        $this->paypal->setApiCredentials($paypalConfig);

        // Obtain Access Token
        try {
            $accessToken = $this->paypal->getAccessToken();
        } catch (\Exception $e) {
            Log::error('Failed to obtain PayPal Access Token: ' . $e->getMessage());
            throw $e;
        }

        return $this->paypal;
    }

    /**
     * Verify that the configured PayPal webhook endpoint exists.
     *
     * @return bool
     * @throws \Exception
     */
    public function verifyWebhookEndpoint()
    {
        try {
            $paypalClient = $this->initializePayPal();

            $webhookId = config('paypal.webhook_id');
            // Log::debug('Verifying PayPal Webhook Endpoint.', ['webhook_id' => $webhookId]);

            $webhookData = $paypalClient->listWebhooks();
            // Log::debug('Retrieved PayPal Webhooks.', ['webhook_count' => count($webhookData['webhooks'] ?? [])]);

            $webhooks = $webhookData['webhooks'] ?? [];

            $webhookExists = collect($webhooks)->first(function ($webhook) use ($webhookId) {
                return ($webhook['id'] ?? null) === $webhookId;
            });

            if (!$webhookExists) {
                $message = 'Invalid PayPal Webhook configuration: Webhook ID not found.';
                // Log::error($message, ['configured_webhook_id' => $webhookId, 'available_webhooks' => $webhooks]);
                throw new \Exception($message);
            }

            Log::info('PayPal Webhook verification successful.');
            return true;
        } catch (\Exception $e) {
            // Log::error('PayPal Webhook Verification Error: ' . $e->getMessage(), [
            //     'exception' => $e
            // ]);
            throw $e;
        }
    }



    public function createPayPalPayment($user, $plan, $payment)
    {
        try {
            $this->initializePayPal();

            $price = number_format($plan->price, 2, '.', '');

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => (string)$payment->id,
                        'description' => "Subscription to {$plan->name} Plan",
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => (string)$price,
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => 'USD',
                                    'value' => (string)$price
                                ]
                            ]
                        ],
                        'items' => [
                            [
                                'name' => $plan->name,
                                'description' => "Access to premium features with {$plan->name} Plan",
                                'quantity' => '1',
                                'unit_amount' => [
                                    'currency_code' => 'USD',
                                    'value' => (string)$price
                                ],
                            ]
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('payment.close'),
                    'cancel_url' => route('payment.close')
                ]
            ];

            $order = $this->paypal->createOrder($orderData);

            if (isset($order['error'])) {
                throw new \Exception($order['error']['message'] ?? 'Failed to create PayPal order');
            }

            $payment->update([
                'gateway_subscription_id' => $order['id']
            ]);

            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return $link['href'];
                }
            }

            throw new \Exception('Failed to create PayPal order: No approval URL found');

        } catch (\Exception $e) {
            Log::error('PayPal Payment Error', [
                'error' => $e->getMessage(),
                'user' => $user->id,
                'plan' => $plan->id,
                'amount' => $plan->price
            ]);
            throw $e;
        }
    }

}
