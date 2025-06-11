<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use App\Traits\PlanPriceCalculator;
use Illuminate\Database\DeadlockException;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\Attributes\On;
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
    public $upgradeCalculation = null;
    public $time_zone;
    protected $listeners = [
        'confirmed' => 'handleConfirmed',
        'cancelled' => 'handleCancelled'
    ];

    public function updatedSelectedPlan($value)
    {
        $this->validateOnly('selectedPlan');

        $this->upgradeCalculation = $this->calculateUpgradeCost($this->selectedPlan);
    }

    public function mount(){

        $this->time_zone = auth()->user()->timezone ?? config('app.timezone');

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

                    $selectedPlan = Plan::find($value);
                    $currentPlanPrice = $this->getCurrentPlanPrice();

                    if ($currentPlanPrice && $selectedPlan && $selectedPlan->price < $currentPlanPrice) {
                        $fail("You cannot downgrade to a plan with a lower price.");
                    }
                },
            ],
        ];
    }



    // Start with confirm the user with his step
    public function initiatePayment()
    {


        if (!auth()->user()->hasVerifiedEmail()) {
            $this->alert('warning', 'Please verify your email address before subscribing.',['position' => 'center']);
            return;
        }

        $this->validate();
        $user = auth()->user();

        // Check if user has an active subscription
        if ($user && $user->subscription && $user->subscription->plan->id != 1) {
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
        $this->handleConfirmed();
    }

    // New method to handle confirmation
    public function handleConfirmed()
    {
        $this->validateOnly('selectedPlan');
        $this->dispatch('payment-method',$this->selectedPlan);
    }

    // New method to handle cancellation
    public function handleCancelled()
    {
        $this->selectedPlan = null;
    }

    // Paypal payment
    #[On('paypal-payment')]
    public function proceedWithPayment()
    {
        // Log::info('proceedWithPayment called');

        try {
            // Verify webhook first
            $this->verifyWebhookEndpoint();

            DB::beginTransaction();

            $user = auth()->user();
            $plan = Plan::findOrFail($this->selectedPlan);

            $this->upgradeCalculation = $this->calculateUpgradeCost($plan->id);
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
            $this->alert('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function getCurrentPlanId()
    {
        $user = auth()->user();
        if ($user && $user->subscription) {
            return $user->subscription->plan_id;
        }
        return null;
    }

    public function getCurrentPlanPrice()
    {
        $user = auth()->user();
        if ($user && $user->subscription) {
            return $user->subscription->plan->price;
        }
        return null;
    }

    public function render()
    {
        $currentPlanId = $this->getCurrentPlanId();
        $currentPlanPrice = $this->getCurrentPlanPrice();

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
            'currentPlanPrice'=>$currentPlanPrice,
            'upgradeCalculation' => $this->upgradeCalculation
        ])->layout('layouts.app',['title' => 'Plans']);
    }
}
