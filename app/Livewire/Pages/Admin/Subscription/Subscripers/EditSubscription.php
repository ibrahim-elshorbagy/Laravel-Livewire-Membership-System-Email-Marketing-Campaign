<?php

namespace App\Livewire\Pages\Admin\Subscription\Subscripers;

use Livewire\Component;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use App\Models\Payment\Payment;
use App\Notifications\Paypal\AdminSubscriptionCancelledNotification;
use App\Notifications\Paypal\AdminSubscriptionReactiveNotification;
use App\Notifications\Paypal\AdminSubscriptionSuppressNotification;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

class EditSubscription extends Component
{
     use LivewireAlert;

    public Subscription $subscription;
    public ?Payment $payment = null;
    public $amount;
    public $status;
    public $grace_days_ended_at;
    public $server_status;
    public $started_at;
    public $expired_at;

    public $selectedPlan;
    public $availablePlans;
    public $currentPlan;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:pending,approved,failed,cancelled,refunded',
        'grace_days_ended_at' => 'nullable|date|after:now',
        'server_status' => 'required|in:running,hold',
        'started_at' => 'required|date',
        'expired_at' => 'nullable|date|after:started_at',
        'selectedPlan' => 'required|exists:plans,id',
    ];

    public function mount(Subscription $subscription)
    {
        // dd($subscription);
        $this->subscription = Subscription::withoutGlobalScope(SuppressingScope::class)
            ->with('subscriber')
            ->findOrFail($subscription->id);

        $this->payment = Payment::where('subscription_id', $subscription->id)->latest()->first();

        // Initialize payment-related properties only if payment exists
        if ($this->payment) {
            $this->amount = $this->payment->amount;
            $this->status = $this->payment->status;
        }

        $this->grace_days_ended_at = $subscription->grace_days_ended_at?->format('Y-m-d\TH:i');
        $this->server_status = $subscription->server_status ?? 'running';
        $this->started_at = $subscription->started_at->format('Y-m-d');
        $this->expired_at = $subscription->expired_at?->format('Y-m-d');

        // Load plans
        $this->currentPlan = $subscription->plan;
        $this->selectedPlan = $this->currentPlan->id;
        $this->loadAvailablePlans();
    }

    public function loadAvailablePlans()
    {
        $this->availablePlans = Plan::where('id', '!=', $this->currentPlan->id)
            ->get();
    }

    public function switchPlan()
    {
        $this->validate([
            'selectedPlan' => 'required|exists:plans,id',
        ]);

        DB::beginTransaction();
        try {
            $newPlan = Plan::findOrFail($this->selectedPlan);
            $subscriber = $this->subscription->subscriber;

            // Switch to new plan (this will suppress current subscription and create new one)
            $subscriber->switchTo($newPlan);

            // Get the new subscription
            $newSubscription = $subscriber->lastSubscription();

            // Update the payment record with the new subscription ID
            if ($this->payment) {
                $this->payment->update([
                    'subscription_id' => $newSubscription->id,
                    'plan_id' => $newPlan->id
                ]);
            }

            DB::commit();
            Session::flash('success', 'Plan switched successfully.');

            // Redirect to the edit page with the new subscription ID
            return $this->redirect(
                route('admin.subscriptions.edit', ['subscription' => $newSubscription->id]),
                navigate: true
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to switch plan: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function updatePayment()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,failed,cancelled,refunded',
        ]);

        DB::beginTransaction();
        try {
            if ($this->payment) {
                // Update existing payment
                $this->payment->update([
                    'amount' => $this->amount,
                    'status' => $this->status,
                ]);
            } else {
                // Create new payment record
                $this->payment = Payment::create([
                    'user_id' => $this->subscription->subscriber->id,
                    'plan_id' => $this->subscription->plan_id,
                    'subscription_id' => $this->subscription->id,
                    'gateway' => 'cash',
                    'gateway_subscription_id' => null,
                    'transaction_id' => null,
                    'amount' => $this->amount,
                    'currency' => 'USD',
                    'status' => $this->status,
                ]);
            }

            DB::commit();
            $this->dispatch('close-modal', 'edit-payment');
            $this->alert('success', 'Payment updated successfully.', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to update payment.', ['position' => 'bottom-end']);
        }
    }

    public function updateSubscriptionDetails()
    {
        $this->validate([
            'server_status' => 'required|in:running,hold',
            'started_at' => 'required|date',
            'expired_at' => 'nullable|date|after:started_at',
            'grace_days_ended_at' => 'nullable|date|after:now',
        ]);

        DB::beginTransaction();
        try {
            $this->subscription->update([
                'server_status' => $this->server_status,
                'started_at' => $this->started_at,
                'expired_at' => $this->expired_at,
                'grace_days_ended_at' => $this->grace_days_ended_at,
            ]);

            DB::commit();
            $this->dispatch('close-modal', 'edit-subscription');
            $this->alert('success', 'Subscription details updated successfully.', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to update subscription details.' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function cancelSubscription()
    {
        DB::beginTransaction();
        try {
            $this->subscription->update([
                'canceled_at' => now(),
                'suppressed_at' => null,
                'server_status' => 'hold',
            ]);
            $this->subscription->subscriber->notify(new AdminSubscriptionCancelledNotification($this->subscription));
            $this->alert('success', 'Subscription cancelled successfully.' , ['position' => 'bottom-end']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to cancel subscription.'. $e->getMessage(), ['position' => 'bottom-end']);
        }

    }

    public function suppressSubscription()
    {
        DB::beginTransaction();
        try {
            $this->subscription->suppress();
            $this->subscription->subscriber->notify(new AdminSubscriptionSuppressNotification($this->subscription));
            DB::commit();
            $this->alert('success', 'Subscription suppressed successfully.', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to suppress subscription.'. $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function reActiveSubscription()
    {
        DB::beginTransaction();
        try {
            $this->subscription->update([
                'suppressed_at' => null,
                'canceled_at' => null,
            ]);
            $this->subscription->subscriber->notify(new AdminSubscriptionReactiveNotification($this->subscription));

            DB::commit();
            $this->alert('success', 'Subscription reactivated successfully.', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to reactivate subscription.', ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.subscription.subscripers.edit-subscription')
            ->layout('layouts.app');
    }
}
