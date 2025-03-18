<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Offline\OfflinePaymentMethod;
use App\Models\Payment\Payment;
use App\Models\Payment\PaymentImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use LucasDotVin\Soulbscription\Models\Plan;
use Livewire\Attributes\On;
use App\Traits\PlanPriceCalculator;

class PaymentMethodSelection extends Component
{
    use LivewireAlert, WithFileUploads;
    use  PlanPriceCalculator;

    public $selectedMethod = 'paypal';
    public $offlineMethod = null;
    public $images = [];
    public $payment;
    public $upgradeCalculation;
    public $previewImageUrl;
    public $instructions;
    public $requiresImage = false;
    public $offlineMethods ;
    public $selectedPlan;

    public function rules(): array
    {
        return [
            'selectedMethod' => 'required|in:paypal,' . implode(',', OfflinePaymentMethod::where('active', true)->pluck('slug')->toArray()),
            'images.*' => 'image|mimes:jpeg,jpg,png|max:2048'
        ];
    }


    #[On('payment-method')]
    public function setSelectedPlan($selectedPlan)
    {
        $this->selectedPlan = $selectedPlan;
        $this->dispatch('open-modal', 'payment-method-modal');
    }

    public function mount()
    {
        $this->offlineMethods = OfflinePaymentMethod::where('active',true)->get();
    }



    public function removeImage($index)
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            $this->images = array_values($this->images);
        }
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
    public function processPayment()
    {
        $this->validate();

        if ($this->selectedMethod === 'paypal') {
            $this->dispatch('paypal-payment');
            return;
        }


        try {
            DB::beginTransaction();


            $user = auth()->user();
            $plan = Plan::findOrFail($this->selectedPlan);

            $this->calculateUpgradeCost();
            $PaymentCalculation =$this->upgradeCalculation['upgrade_cost'];
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'gateway' => $this->selectedMethod,
                'amount' => $PaymentCalculation,
                'currency' => 'USD',
                'status' => 'pending',
            ]);

            $userId = auth()->id();

            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    $path = $image->store('users/' . $userId . '/payments/' . $payment->id, 'public');
                    $payment->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            $this->alert('success', 'Payment information submitted successfully!');
            $this->redirect(route('user.my-transactions'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.pages.user.subscription.payment-method-selection');
    }
}
