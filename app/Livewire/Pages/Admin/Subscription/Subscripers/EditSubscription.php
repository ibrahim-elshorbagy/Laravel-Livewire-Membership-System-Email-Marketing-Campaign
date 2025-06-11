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
use Carbon\Carbon;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;

use Illuminate\Support\Facades\Mail;
use App\Mail\BaseMail;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Support\Facades\Log;

class EditSubscription extends Component
{
     use LivewireAlert;

    public Subscription $subscription;
    public ?Payment $payment = null;
    public $amount;
    public $status;
    public $grace_days_ended_at;
    public $started_at;
    public $expired_at;

    public $selectedPlan;
    public $availablePlans;
    public $currentPlan;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:pending,approved,failed,cancelled,refunded',
        'grace_days_ended_at' => 'nullable|date|after:now',
        'started_at' => 'required|date',
        'expired_at' => 'nullable|date|after:started_at',
        'selectedPlan' => 'required|exists:plans,id',
    ];

    public function mount(Subscription $subscription)
    {
        // If it's already a model instance, use it directly
        if ($subscription instanceof Subscription) {
            $this->subscription = $subscription;
        } else {
            // Otherwise, query it without global scopes
            $this->subscription = Subscription::withoutGlobalScopes([SuppressingScope::class, StartingScope::class])
                ->with('subscriber')
                ->findOrFail($subscription);
        }


        $this->payment = Payment::where('subscription_id', $subscription->id)->latest()->first();

        // Initialize payment-related properties only if payment exists
        if ($this->payment) {
            $this->amount = $this->payment->amount;
            $this->status = $this->payment->status;
        }

        $this->grace_days_ended_at = $subscription->grace_days_ended_at?->format('Y-m-d\TH:i');
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
            $newSubscription = $subscriber->subscription;

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
            'started_at' => 'required|date',
            'expired_at' => 'nullable|date|after:started_at',
            'grace_days_ended_at' => 'nullable|date|after:now',
        ]);

        DB::beginTransaction();
        try {
            $this->subscription->update([
                'started_at' => $this->started_at,
                'expired_at' => $this->expired_at,
                'grace_days_ended_at' => $this->grace_days_ended_at,
                'suppressed_at' => null,
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
                'grace_days_ended_at' => null,
            ]);

            $slug ='admin-cancelled-subscription';
            $emailTemplate = SystemEmail::where('slug', $slug)->select('id')->first();
            if ($emailTemplate) {

                $user = $this->subscription->subscriber;

                $mailData = [
                    'slug' => 'admin-cancelled-subscription',
                    'user_id' => $user->id,
                    'subscription_id' => $this->subscription->id,
                ];
                Mail::to($user->email)->queue(new BaseMail($mailData));


            }else{
                $this->subscription->subscriber->notify(new AdminSubscriptionCancelledNotification($this->subscription));
            }



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


            $slug ='admin-suppressed-subscription';
            $emailTemplate = SystemEmail::where('slug', $slug)->select('id')->first();

            if ($emailTemplate) {

                $user = $this->subscription->subscriber;
                $mailData = [
                    'slug' => 'admin-suppressed-subscription',
                    'user_id' => $user->id,
                    'subscription_id' => $this->subscription->id,
                ];
                Mail::to($user->email)->queue(new BaseMail($mailData));

            }else{
                $this->subscription->subscriber->notify(new AdminSubscriptionSuppressNotification($this->subscription));
            }


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


            $slug ='admin-reactivated-subscription';
            $emailTemplate = SystemEmail::where('slug', $slug)->select('id')->first();


            if ($emailTemplate) {

                $user = $this->subscription->subscriber;
                $mailData = [
                    'slug' => 'admin-reactivated-subscription',
                    'user_id' => $user->id,
                    'subscription_id' => $this->subscription->id,
                ];
                Mail::to($user->email)->queue(new BaseMail($mailData));

            }else{
                $this->subscription->subscriber->notify(new AdminSubscriptionReactiveNotification($this->subscription));
            }


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
            ->layout('layouts.app',['title' => 'Edit Subscription']);
    }
}
