<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Services\PayPalLogger;
use Illuminate\Support\Facades\DB;

trait PaypalPaymentService
{
    protected $paypal;

    protected function logPayPalResponse($userId, $status, array $responseData)
    {
        DB::table('paypal_responses')->insert([
            'user_id' => $userId,
            'transaction_id' => $responseData['order_id'] ?? $responseData['capture_id'] ?? null,
            'status' => $status,
            'response_data' => json_encode($responseData),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Initialize PayPal Client with configuration and obtain access token.
     *
     * @return PayPalClient
     * @throws \Exception
     */
    public function initializePayPal()
    {
        try {
            // Validate PayPal configuration
            $paypalConfig = config('paypal');
            $this->paypal = new PayPalClient();
            $this->paypal->setApiCredentials($paypalConfig);

            // Obtain Access Token
            $accessToken = $this->paypal->getAccessToken();
            return $this->paypal;

        } catch (\Exception $e) {
            PayPalLogger::error('Failed to initialize PayPal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->logPayPalResponse(null, 'failed', [
                'error' => 'PayPal initialization failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
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
            PayPalLogger::info('Verifying PayPal Webhook Endpoint', ['webhook_id' => $webhookId]);

            $webhookData = $paypalClient->listWebhooks();
            PayPalLogger::info('Retrieved PayPal Webhooks', ['webhookData' => $webhookData]);

            $webhooks = $webhookData['webhooks'] ?? [];

            $webhookExists = collect($webhooks)->first(function ($webhook) use ($webhookId) {
                return ($webhook['id'] ?? null) === $webhookId;
            });

            if (!$webhookExists) {
                $message = 'Invalid PayPal Webhook configuration: Webhook Wrong.';
                PayPalLogger::error($message, [
                    'configured_webhook_id' => $webhookId,
                    'webhookExists' => $webhookExists,
                    'webhookData' => $webhookData
                ]);

                $this->logPayPalResponse(null, 'failed', [
                    'error' => $message,
                    'webhook_id' => $webhookId,
                    'webhookData' => $webhookData
                ]);

                throw new \Exception($message);
            }

            PayPalLogger::info('PayPal Webhook verification successful');
            return true;

        } catch (\Exception $e) {
            // There is error inside this file

            PayPalLogger::error('Webhook verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Webhook verification failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
                    'cancel_url' => route('welcome')
                ]
            ];

            PayPalLogger::info('Creating PayPal order', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'amount' => $price
            ]);

            $order = $this->paypal->createOrder($orderData);

            if (isset($order['error'])) {
                $this->logPayPalResponse($user->id, 'error', [
                    'order_id' => null,
                    'error' => $order['error']['message'] ?? 'Unknown error',
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_id' => $payment->id
                ]);
                PayPalLogger::error('Failed to create PayPal order', [
                    'error' => $order['error']['message'] ?? 'Unknown error',
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_id' => $payment->id
                ]);
                throw new \Exception($order['error']['message'] ?? 'Failed to create PayPal order');
            }

            $payment->update([
                'gateway_subscription_id' => $order['id']
            ]);

            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    PayPalLogger::info('PayPal order created successfully', [
                        'order_id' => $order['id'],
                        'payment_id' => $payment->id
                    ]);
                    return $link['href'];
                }
            }

            throw new \Exception('Failed to create PayPal order: No approval URL found');

        } catch (\Exception $e) {
            PayPalLogger::error('PayPal Payment Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'amount' => $plan->price
            ]);
            throw $e;
        }
    }
}
