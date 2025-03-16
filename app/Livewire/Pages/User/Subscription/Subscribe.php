<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use App\Traits\PlanPriceCalculator;
use Illuminate\Database\DeadlockException;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscriber;
use Illuminate\Support\Facades\Log;

class Subscribe extends Component
{
    use LivewireAlert, PaypalPaymentService, PlanPriceCalculator;

    public $selectedPlan;
    public $selectedTab = 'monthly';
    public $paymentUrl;
    public $isProcessing = false;
    public $upgradeCalculation = null;

    protected $listeners = [
        'confirmed' => 'handleConfirmed',
        'cancelled' => 'handleCancelled'
    ];

    public function updatedSelectedPlan($value)
    {
        $this->validateOnly('selectedPlan');
        $this->calculateUpgradeCost();
    }

    protected function calculateUpgradeCost()
    {
        $user = auth()->user();
        if ($user && $this->selectedPlan) {
            $currentSubscription = $user->lastSubscription();
            if ($currentSubscription) {
                $newPlan = Plan::find($this->selectedPlan);
                if ($newPlan) {
                    $this->upgradeCalculation = $this->calculateUpgradePrice($newPlan, $currentSubscription);
                }
            }
        }
    }

    protected function rules()
    {
        return [
            'selectedPlan' => [
                'required',
                'exists:plans,id',
                function ($attribute, $value, $fail) {
                    $currentPlanId = $this->getCurrentPlanId();
                    if ($currentPlanId === (int)$value) {
                        $fail("You can't select your current plan.");
                    }
                },
            ],
        ];
    }



    public function initiatePayment()
    {
        $this->isProcessing = true;


        if (!auth()->user()->hasVerifiedEmail()) {
            $this->alert('warning', 'Please verify your email address before subscribing.',['position' => 'center']);
            return;
        }

        $this->validate();
        $user = auth()->user();

        // Check if user has an active subscription
        if ($user && $user->lastSubscription() && $user->lastSubscription()->plan->id != 1) {
            // Log::info('Showing confirmation dialog');

            // Show confirmation dialog with correct event handling
            $this->alert('warning', 'Active Subscription', [
                    'title'             => 'Confirm Subscription Change',
                    'text'              => 'You currently have an active subscription. Do you want to replace it with the new plan?',
                    'showConfirmButton' => true,
                    'confirmButtonText' => 'Replace Subscription',
                    'showCancelButton'  => true,
                    'cancelButtonText'  => 'Keep Current Plan',
                    'reverseButtons'    => true,
                    'position'          => 'center',
                    'timer'             => null,
                    'toast'             => false,
                    'showLoaderOnConfirm' => true,
                    'allowOutsideClick' => false,
                    'onConfirmed' => 'confirmed',
                    'onDismissed' => 'cancelled',
                ]);

            return;
        }

        // If no active subscription, proceed directly
        $this->proceedWithPayment();
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
        // Log::info('Cancellation received');
        $this->isProcessing = false;
        $this->selectedPlan = null;
        $this->alert('info', 'Subscription change cancelled');
    }
    public function proceedWithPayment()
    {
        // Log::info('proceedWithPayment called');
        $this->isProcessing = true;

        try {
            // Verify webhook first
            $this->verifyWebhookEndpoint();

            DB::beginTransaction();

            $user = auth()->user();
            $plan = Plan::findOrFail($this->selectedPlan);

            $this->calculateUpgradeCost();
            $PaymentCalculation =$this->upgradeCalculation['upgrade_cost'];
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'gateway' => 'paypal',
                'amount' => $PaymentCalculation,
                'currency' => 'USD',
                'status' => 'pending',
            ]);

            // Create PayPal subscription and get approval URL
            $approvalUrl = $this->createPayPalPayment($user, $plan,$PaymentCalculation, $payment);

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

    public function getCurrentPlanId()
    {
        $user = auth()->user();
        if ($user && $user->lastSubscription()) {
            return $user->lastSubscription()->plan_id;
        }
        return null;
    }

    public function render()
    {
        $currentPlanId = $this->getCurrentPlanId();

        $monthlyPlans = Plan::with('features')
            ->where('periodicity_type', 'Month')
            ->get();

        $yearlyPlans = Plan::with('features')
            ->where('periodicity_type', 'Year')
            ->where('id', '!=', 1)
            ->get();

        return view('livewire.pages.user.subscription.subscribe', [
            'monthlyPlans' => $monthlyPlans,
            'yearlyPlans' => $yearlyPlans,
            'currentPlanId' => $currentPlanId,
            'upgradeCalculation' => $this->upgradeCalculation
        ])->layout('layouts.app',['title' => 'Plans']);
    }
}
