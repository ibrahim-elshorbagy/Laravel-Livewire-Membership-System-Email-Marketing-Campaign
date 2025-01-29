<?php

namespace App\Handler;

use App\Models\Payment\Payment;
use App\Notifications\Paypal\SubscriptionActivatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalWebhookJob extends ProcessWebhookJob
{
    protected $paypal;

    protected function initializePayPal()
    {
        if (!$this->paypal) {
            $this->paypal = new PayPalClient;
            $this->paypal->setApiCredentials(config('paypal'));
            $this->paypal->getAccessToken();
        }
        return $this->paypal;
    }
    public function handle()
    {
        $event = $this->webhookCall->payload;
        $eventType = $event['event_type'] ?? null;
        $resource = $event['resource'] ?? null;

        Log::info('PayPal Webhook Received', [
            'event_type' => $eventType,
            'resource_id' => $resource['id'] ?? null,
        ]);

        if (!$eventType || !$resource) {
            Log::warning('Invalid webhook payload', ['payload' => $event]);
            return;
        }

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                $this->handleOrderApproved($resource);
                break;

            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCompleted($resource);
                break;

            default:
                Log::info("Unhandled PayPal event type: {$eventType}");
                break;
        }
    }

    protected function handleOrderApproved($resource)
    {
        $orderId = $resource['id'] ?? null;

        if (!$orderId) {
            Log::warning('Order ID not found in resource', ['resource' => $resource]);
            return;
        }

        try {
            $payment = Payment::where('gateway_subscription_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                Log::warning('Payment not found for order', ['order_id' => $orderId]);
                return;
            }

            Log::info('CHECKOUT.ORDER.APPROVED : Order Approved by User', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'plan' => $payment->plan->name,
                'amount' => $resource['purchase_units'][0]['amount']['value'] ?? 'unknown',
                'status' => $resource['status'] ?? 'unknown'
            ]);

            // Initialize PayPal and capture payment
            $paypal = $this->initializePayPal();
            $captureResponse = $paypal->capturePaymentOrder($orderId);

            Log::info('Payment Capture Response', [
                'response' => $captureResponse,
                'payment_id' => $payment->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing order approval', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function handlePaymentCompleted($resource)
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        if (!$orderId) {
            Log::warning('Order ID not found in payment completion', ['resource' => $resource]);
            return;
        }

        try {
            $payment = Payment::where('gateway_subscription_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                Log::warning('Payment not found for completed payment', [
                    'order_id' => $orderId,
                    'capture_id' => $resource['id'] ?? null
                ]);
                return;
            }

            DB::transaction(function () use ($payment, $resource) {
                // Create subscription
                $subscription = $payment->user->subscribeTo($payment->plan);

                // Update payment with capture details
                $payment->update([
                    'subscription_id' => $subscription->id,
                    'status' => 'completed',
                    'paid' => true,
                    'transaction_id' =>  $resource['id'] ?? null,
                ]);

                // Notify user
                $payment->user->notify(new SubscriptionActivatedNotification($subscription));

                Log::info('handlePaymentCompleted : Payment Processed Successfully', [
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscription->id,
                    'capture_id' => $resource['id'] ?? null,
                    'amount' => $resource['amount']['value'] ?? null,
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Error processing payment completion', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }



}
