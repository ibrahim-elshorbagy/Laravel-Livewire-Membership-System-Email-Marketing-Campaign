<?php

namespace App\Livewire\Pages\Admin\Transactions;

use App\Models\Payment\Offline\OfflinePaymentMethod;
use Livewire\Component;
use App\Models\Payment\Payment;
use App\Models\User;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Traits\SubscriptionManagementTrait;
use App\Traits\PlanPriceCalculator;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use App\Models\EmailList;

class EditPayment extends Component
{
    use LivewireAlert, SubscriptionManagementTrait, PlanPriceCalculator;

    public Payment $payment;
    public $user; //admin can see anything about user
    public $plan;
    public $subscription;
    public $calculatedDates;

    public $gateway_subscription_id;
    public $transaction_id;
    // Form fields
    public $amount;
    public $status;
    public $gateway;
    public $previewImageUrl;

    public $offlinePaymentMethods;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:pending,approved,failed,cancelled,refunded',
        'gateway' => 'required',
        'gateway_subscription_id' => 'nullable',
        'transaction_id' => 'nullable',
    ];

    public function getImagesProperty()
    {
        return $this->payment->images;
    }
// In the mount function, add:
    public function mount(Payment $payment) {
        $this->payment = $payment;
        $this->user = $payment->user;
        $this->plan = $payment->plan;
        if($payment->subscription_id){
            $this->subscription = Subscription::with(['plan'])->withoutGlobalScopes()
            ->find($payment->subscription_id);
        }
        $this->offlinePaymentMethods = OfflinePaymentMethod::select('id', 'name','slug')->get();

        // Initialize form fields
        $this->amount = $payment->amount;
        $this->status = $payment->status;
        $this->gateway = $payment->gateway;
        $this->gateway_subscription_id = $payment->gateway_subscription_id;
        $this->transaction_id = $payment->transaction_id;

        if ($this->subscription) {
            $this->subscription->started_at = $this->subscription->started_at->toDateTimeString();
            $this->subscription->expired_at = $this->subscription->expired_at->toDateTimeString();
            $this->subscription->remaining_time = Carbon::parse($this->subscription->expired_at)->diffForHumans(Carbon::now(), [
                'parts' => 3,
                'join' => true,
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            ]);
        }

        $this->calculateDates();
    }

    public function calculateDates()
    {
        if ($this->user->subscription) {
            $startDate = Carbon::parse($this->user->subscription->started_at);
            $endDate = Carbon::parse($this->user->subscription->expired_at);
            $this->calculatedDates = $this->calculateSubscriptionDates(
                $this->plan,
                $this->user->subscription->plan,
                $startDate,
                $endDate,
                $this->amount
            );
        } else {

            $this->calculatedDates = [
                'will_started_at' => now(),
                'will_expired_at' => $this->plan->periodicity_type === 'Year' ? now()->addYear() : now()->addMonth()
            ];
        }
    }

    public function updatePayment()
    {
        $this->validate();

        try {
            $updateData = [
                'amount' => $this->amount,
                'status' => $this->status,
                'gateway' => $this->gateway,
            ];



            if ($this->gateway != 'paypal') {

                $updateData['gateway_subscription_id'] = $this->gateway_subscription_id;
                $updateData['transaction_id'] = $this->transaction_id;
            }

            $this->payment->update($updateData);

            Session::flash('success', 'Payment updated successfully!');

            return $this->redirect(route('admin.payment.transactions'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Error updating payment: ' . $e->getMessage(), [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function approvePayment()
    {
        try {
            if (!$this->payment) {
                throw new \Exception('Payment not found');
            }

            $subscription = $this->handleSubscriptionChange($this->payment);
            // Reset user consumption metrics
            $subscribersLimitName = Feature::find(1)?->name;
            $emailSendingName = Feature::find(2)?->name;
            $this->payment->user->forceSetConsumption($subscribersLimitName, EmailList::where('user_id', $this->payment->user->id)->count());
            $this->payment->user->forceSetConsumption($emailSendingName, 0);


            $this->payment->update([
                'status' => 'approved',
                'subscription_id' => $subscription->id
            ]);

            Session::flash('success', 'Payment approved and subscription activated successfully!');


            return $this->redirect(route('admin.payment.transactions'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Error approving payment: ' . $e->getMessage(), [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.transactions.edit-payment')
            ->layout('layouts.app',['title' => 'Edit Payment']);
    }
}
