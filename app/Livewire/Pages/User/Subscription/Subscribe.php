<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscriber;

class Subscribe extends Component
{
    use LivewireAlert, PaypalPaymentService;

    public $selectedPlan;
    public $selectedTab = 'monthly';
    public $paymentUrl;
    public $isProcessing = false;

    protected $rules = [
        'selectedPlan' => 'required|exists:plans,id',
    ];
    protected $listeners = ['proceedWithPayment','cancelPayment',];

    public function updatedSelectedPlan($value)
    {
        $this->validateOnly('selectedPlan');
    }

    public function initiatePayment()
    {
        $this->validate();

        $user = auth()->user();


        // Check if user has an active subscription
        if ($user && $user->lastSubscription()) {
            $lastSubscription = $user->lastSubscription();

            // Confirm subscription replacement
           $this->alert('warning', 'Active Subscription', [
                'text' => 'You currently have an active subscription. Do you want to replace it with the new plan?',
                'showConfirmButton' => true,
                'confirmButtonText' => 'Replace Subscription',
                'confirmButtonColor' => 'bg-blue-600 hover:bg-blue-700',
                'showCancelButton' => true,
                'cancelButtonText' => 'Keep Current Plan',
                'cancelButtonColor' => 'bg-red-500 hover:bg-red-600',
                'onConfirmed' => 'proceedWithPayment',
                'onDismissed' => 'cancelPayment',
                'position' => 'center',
                'allowOutsideClick' => false,
                'timer' => null,
                'customClass' => [
                    'popup' => 'rounded-xl shadow-2xl border border-gray-200',
                    'title' => 'text-2xl font-bold text-gray-800',
                    'content' => 'text-base text-gray-600',
                    'confirmButton' => 'px-4 py-2 rounded-lg text-white font-semibold transition-colors',
                    'cancelButton' => 'px-4 py-2 rounded-lg text-white font-semibold transition-colors'
                ],
                'width' => '500px',
            ]);

            return;
        }

        // If no active subscription, proceed directly
        $this->proceedWithPayment();
    }

    public function proceedWithPayment()
    {
        $this->isProcessing = true;

        try {
            // Verify webhook first
            $this->verifyWebhookEndpoint();

            DB::beginTransaction();

            $user = auth()->user();
            $plan = Plan::findOrFail($this->selectedPlan);

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
            $this->isProcessing = false;
            $this->alert('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function cancelPayment()
    {
        $this->isProcessing = false;
        $this->selectedPlan = null;
        $this->alert('info', 'Subscription upgrade cancelled.');
    }

    public function render()
    {
        $monthlyPlans = Plan::with('features')
            ->where('periodicity_type', 'Month')
            ->get();

        $yearlyPlans = Plan::with('features')
            ->where('periodicity_type', 'Year')
            ->get();

        return view('livewire.pages.user.subscription.subscribe', [
            'monthlyPlans' => $monthlyPlans,
            'yearlyPlans' => $yearlyPlans,
        ])->layout('layouts.app');
    }
}
