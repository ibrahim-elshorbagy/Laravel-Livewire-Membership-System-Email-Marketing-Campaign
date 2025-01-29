<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use LucasDotVin\Soulbscription\Models\Plan;

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



    public function updatedSelectedPlan($value)
    {
        $this->validateOnly('selectedPlan');
    }
    public function initiatePayment()
    {
        $this->validate();
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
            $approvalUrl = $this->createPayPalPayment($user,$plan,$payment);

            if (!$approvalUrl) {
                throw new \Exception('PayPal approval URL not found');
            }

            DB::commit();

            $this->paymentUrl = $approvalUrl;
            $this->alert('success', 'Opening PayPal...');

            return redirect()->away($approvalUrl);

            $this->dispatch('paypalPayment', url: $approvalUrl);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->isProcessing = false;
            $this->alert('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
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
