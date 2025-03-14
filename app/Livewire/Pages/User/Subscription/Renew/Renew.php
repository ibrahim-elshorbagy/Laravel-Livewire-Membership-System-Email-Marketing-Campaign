<?php

namespace App\Livewire\Pages\User\Subscription\Renew;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use LucasDotVin\Soulbscription\Models\Plan;
use Illuminate\Support\Facades\Log;

class Renew extends Component
{
    use LivewireAlert, PaypalPaymentService;
    public $paymentUrl;



    protected $listeners = [
        'confirmed' => 'handleConfirmed',
        'cancelled' => 'handleCancelled'
    ];

    public function initiatePayment()
    {
        if (!auth()->user()->hasVerifiedEmail()) {
            $this->alert('warning', 'Please verify your email address before subscribing.',['position' => 'center']);
            return;
        }

        $user = auth()->user();

        // Check if user has an active subscription
        if ($user && $user->lastSubscription() && $user->lastSubscription()->plan->id != 1) {
            // Log::info('Showing confirmation dialog');

            // Show confirmation dialog with correct event handling
            $this->alert('warning', 'Active Subscription', [
                    'title'             => 'Confirm Renew Subscription',
                    'html'              =>  "
                            <ul class='pl-5 space-y-2 list-disc'>
                                <li class='text-left'>Renew will calculate the remaining expiration date from the old subscription with the new one.</li>
                                <li class='text-left'>Your contacts will remain.</li>
                                <li class='text-left'>Your emails per month will be reset.</li>
                            </ul>",
                    'showConfirmButton' => true,
                    'confirmButtonText' => 'Renew',
                    'showCancelButton'  => true,
                    'cancelButtonText'  => 'Cancel',
                    'reverseButtons'    => true,
                    'position'          => 'center',
                    'timer'             => null,
                    'toast'             => false,
                    'showLoaderOnConfirm' => true,
                    'allowOutsideClick' => false,
                    'onConfirmed' => 'confirmed',
                    // 'onDismissed' => 'cancelled',
                    // 'preConfirm' => true
                ]);

            return;
        }

    }

        // New method to handle confirmation
    public function handleConfirmed()
    {
        // Log::info('Confirmation received - proceeding with payment');
        $this->proceedWithPayment();
    }

    // New method to handle cancellation
    public function handleCancelled()
    {

        $this->alert('info', 'Subscription change cancelled',['position' => 'center']);
    }

    public function proceedWithPayment()
    {
        // Log::info('proceedWithPayment called');

        try {
            // Verify webhook first
            $this->verifyWebhookEndpoint();

            DB::beginTransaction();

            $user = auth()->user();
            $plan_id = $user->lastSubscription()->plan_id;

            $plan = Plan::findOrFail($plan_id);

            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'gateway' => 'paypal',
                'amount' => $plan->price,
                'currency' => 'USD',
                'status' => 'pending',
            ]);

            // Create PayPal subscription and get approval URL
            $approvalUrl = $this->createPayPalPayment($user, $plan, $payment);

            if (!$approvalUrl) {
                throw new \Exception('PayPal approval URL not found');
            }


            DB::commit();

            $this->paymentUrl = $approvalUrl;
            $this->alert('success', 'Opening PayPal...');

            return redirect()->away($approvalUrl);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->alert('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.pages.user.subscription.renew.renew');
    }
}
