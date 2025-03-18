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

class EditPayment extends Component
{
    use LivewireAlert;

    public Payment $payment;
    public $user; //admin can see anything about user
    public $plan;
    public $subscription;

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
        $this->subscription = $payment->subscription;
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

            Session::flash('success', 'Payment updated successfully!.');

            return $this->redirect(route('admin.payment.transactions'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Error updating payment: ' . $e->getMessage(), [
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
