<?php

namespace App\Livewire\Pages\Admin\Transactions;

use Livewire\Component;
use App\Models\Payment\Payment;
use App\Models\User;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;

class EditPayment extends Component
{
    use LivewireAlert;

    public Payment $payment;
    public $user;
    public $plan;
    public $subscription;

    // Form fields
    public $amount;
    public $status;
    public $gateway;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:pending,approved,failed,cancelled,refunded',
        'gateway' => 'required|in:paypal,cash',
    ];


// In the mount function, add:
    public function mount(Payment $payment) {
        $this->payment = $payment;
        $this->user = $payment->user;
        $this->plan = $payment->plan;
        $this->subscription = $payment->subscription;

        // Initialize form fields
        $this->amount = $payment->amount;
        $this->status = $payment->status;
        $this->gateway = $payment->gateway;

        if ($this->subscription) {
            $this->subscription->started_at = $this->subscription->created_at->toDateTimeString();
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
            $this->payment->update([
                'amount' => $this->amount,
                'status' => $this->status,
                'gateway' => $this->gateway,
            ]);

            $this->alert('success', 'Payment updated successfully!', [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
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
