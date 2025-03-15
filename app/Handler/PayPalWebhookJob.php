<?php

namespace App\Handler;

use App\Models\EmailList;
use App\Models\Payment\Payment;
use App\Notifications\Paypal\SubscriptionActivatedNotification;
use App\Notifications\Paypal\SubscriptionRenewedNotification;
use App\Services\PayPalLogger;
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

        if (!$eventType || !$resource) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Invalid webhook payload',
                'payload' => $event
            ]);
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
            $payment = Payment::where('gateway_subscription_id', $orderId)
                ->where('status', 'pending')
                ->first();

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

            PayPalLogger::info('Payment Capture Response', [
                'response' => $captureResponse,
                'payment_id' => $payment->id
            ]);

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
            $payment = Payment::where('gateway_subscription_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                $this->logPayPalResponse(null, 'failed', [
                    'error' => 'No payment Found for this order (Order Completed)',
                    'order_id' => $orderId,
                    'capture_id' => $resource['id'] ?? null
                ]);
                return;
            }


            DB::transaction(function () use ($payment, $resource) {
                if ($payment->user->lastSubscription()) {

                    if($payment->user->lastSubscription()->plan->id == $payment->plan_id){// This only work if he renew the subscription

                        $subscription = $payment->user->lastSubscription()->renew();
                        $payment->user->forceSetConsumption('Subscribers Limit',EmailList::where('user_id', $payment->user->id)->count());
                        $payment->user->forceSetConsumption('Email Sending',0);

                        PayPalLogger::info('Renew Subscription ', [
                            'payment_id' => $payment->id,
                            'subscription_id' => $subscription->id,
                        ]);

                        $payment->user->notify(new SubscriptionRenewedNotification($subscription));

                    }else{ //New Subscription or Upgrade

                        $payment->user->lastSubscription()->suppress();
                        $subscription = $payment->user->subscribeTo($payment->plan);
                        $payment->user->forceSetConsumption('Subscribers Limit',EmailList::where('user_id', $payment->user->id)->count());
                        $payment->user->forceSetConsumption('Email Sending',0);

                        // Notify user
                        $payment->user->notify(new SubscriptionActivatedNotification($subscription));

                        PayPalLogger::info('Upgrade Subscription ', [
                            'payment_id' => $payment->id,
                            'subscription_id' => $subscription->id,
                        ]);
                    }
                }
                // Create subscription

                // Update payment with capture details
                $payment->update([
                    'subscription_id' => $subscription->id,
                    'status' => 'approved',
                    'transaction_id' =>  $resource['id'] ?? null,
                ]);


                $this->logPayPalResponse($payment->user_id, 'success', [
                    'message' => 'Payment processed successfully',
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscription->id,
                    'capture_id' => $resource['id'] ?? null,
                    'amount' => $resource['amount']['value'] ?? null
                ]);
            });

        } catch (\Exception $e) {
            $this->logPayPalResponse(null, 'failed', [
                'error' => 'Error processing payment completion: ' . $e->getMessage(),
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
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
