<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use App\Services\PaypalPaymentService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MySubscription extends Component
{
    use LivewireAlert, PaypalPaymentService;

    public $subscription;
    public $user;
    public $isProcessing = false;

    public function mount()
    {
        $this->user = auth()->user();
        $this->subscription = $this->user->lastSubscription();

        if ($this->subscription) {
            $this->subscription->started_at = $this->subscription->created_at->toDateTimeString();
            $this->subscription->expired_at = $this->subscription->expired_at->toDateTimeString();
            $this->subscription->remaining_time = Carbon::now()->diffForHumans($this->subscription->expired_at, [
                'parts' => 3,
                'join' => true,
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            ]);
        }
    }

    public function cancelSubscription()
    {
        $this->isProcessing = true;

        try {
            // Verify webhook first

            DB::beginTransaction();

            $subscription = $this->subscription;

            // Get the associated payment
            $payment = Payment::where('user_id', $this->user->id)
                ->where('subscription_id', $subscription->id)
                ->where('status', 'completed')
                ->latest()
                ->first();

            // Cancel subscription locally
            $subscription->cancel();

            DB::commit();

            $this->alert('success', 'Your subscription has been successfully cancelled.');
            $this->subscription = $this->user->fresh()->subscription;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to cancel subscription: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }


    public function render()
    {
        return view('livewire.pages.user.subscription.my-subscription')->layout('layouts.app');
    }
}
