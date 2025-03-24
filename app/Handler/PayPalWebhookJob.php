<?php

namespace App\Handler;

use App\Models\EmailList;
use App\Models\Payment\Payment;
use App\Services\PayPalLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Traits\SubscriptionManagementTrait;



class PayPalWebhookJob extends ProcessWebhookJob
{
    protected $paypal;
    use SubscriptionManagementTrait;

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

        if (!$eventType || !$resource) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Invalid webhook payload',
                'payload' => $event
            ]);
            return;
        }

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED': //This Hapends when the user approves the payment on PayPal But the payment is not completed
                $this->handleOrderApproved($resource);
                break;

            case 'PAYMENT.CAPTURE.COMPLETED': //This Hapends when the payment is completed
                $this->handlePaymentCompleted($resource);
                break;

            default:
                PayPalLogger::info("Unhandled PayPal event type: {$eventType}");
                break;
        }
    }

    protected function handleOrderApproved($resource)
    {
        $orderId = $resource['id'] ?? null;

        if (!$orderId) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Order ID not found in resource',
                'resource' => $resource
            ]);
            return;
        }

        try {
            $payment = Payment::where('gateway_subscription_id', $orderId)->first();

            if (!$payment) {
                $this->logPayPalResponse(null, 'failed', [
                    'error' => 'No payment Found for this order (Order Approved)',
                    'order_id' => $orderId
                ]);
                return;
            }

            PayPalLogger::info('CHECKOUT.ORDER.APPROVED : Order Approved by User', [
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

            // Update payment status to processing
            $payment->update([
                'status' => 'processing'
            ]);

            PayPalLogger::info('Payment Capture Response', [
                'response' => $captureResponse,
                'payment_id' => $payment->id
            ]);

            // Check if there's an error in the capture response
            if (isset($captureResponse['error'])) {
                $errorDetails = $captureResponse['error'];

                // Update payment status to failed
                $payment->update([
                    'status' => 'failed',
                    'transaction_id' => $orderId
                ]);

                $this->logPayPalResponse($payment->user_id, 'failed', [
                    'error' => 'Payment capture failed: ' . ($errorDetails['message'] ?? 'Unknown error'),
                    'error_details' => $errorDetails,
                    'order_id' => $orderId
                ]);
                PayPalLogger::error('Payment capture failed', [
                    'error_details' => $errorDetails,
                    'order_id' => $orderId,
                    'payment_id' => $payment->id
                ]);
                return;
            }

            if (!isset($captureResponse['status']) || $captureResponse['status'] !== 'COMPLETED') {
                PayPalLogger::error('Unexpected capture response status', [
                    'response' => $captureResponse,
                    'order_id' => $orderId,
                    'payment_id' => $payment->id
                ]);
            }

        } catch (\Exception $e) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Error processing order approval: ' . $e->getMessage(),
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function handlePaymentCompleted($resource)
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        if (!$orderId) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Order ID not found in payment completion',
                'resource' => $resource
            ]);
            return;
        }

        try {
            $payment = Payment::where('gateway_subscription_id', $orderId)->first();

            if (!$payment) {
                $this->logPayPalResponse(null, 'failed', [
                    'error' => 'No payment Found for this order (Order Completed)',
                    'order_id' => $orderId,
                    'capture_id' => $resource['id'] ?? null
                ]);

                PayPalLogger::info('PAYMENT.CAPTURE.COMPLETED : No payment Found for this order (Order Completed)', [
                    'order_id' => $orderId,
                    'amount' => $resource['amount']['value'] ?? 'unknown',
                    'status' => $resource['status'] ?? 'unknown'
                ]);

                return;
            }


            DB::transaction(function () use ($payment, $resource) {
                try {



                    $subscription = $this->handleSubscriptionChange($payment);

                    // Reset user consumption metrics
                    $payment->user->forceSetConsumption('Subscribers Limit', EmailList::where('user_id', $payment->user->id)->count());
                    $payment->user->forceSetConsumption('Email Sending', 0);


                    $payment->update([
                        'subscription_id' => $subscription->id,
                        'status' => 'approved',
                        'transaction_id' => $resource['id'] ?? null,
                    ]);

                    PayPalLogger::info('Renew Subscription ', [
                        'payment_id' => $payment->id,
                        'subscription_id' => $subscription->id,
                    ]);

                    $this->logPayPalResponse($payment->user_id, 'success', [
                        'message' => 'Payment processed successfully',
                        'payment_id' => $payment->id,
                        'subscription_id' => $subscription->id,
                        'capture_id' => $resource['id'] ?? null,
                        'amount' => $resource['amount']['value'] ?? null
                    ]);
                } catch (\Exception $e) {
                    PayPalLogger::error('Transaction failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            });
        } catch (\Exception $e) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Error processing payment completion: ' . $e->getMessage(),
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // This will ensure the job is moved to failed_jobs table
        }
    }

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

}
