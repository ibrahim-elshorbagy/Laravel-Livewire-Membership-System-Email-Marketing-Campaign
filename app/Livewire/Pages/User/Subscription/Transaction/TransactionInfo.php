<?php

namespace App\Livewire\Pages\User\Subscription\Transaction;

use App\Models\Payment\Payment;
use App\Models\Payment\PaymentImage;
use App\Models\Payment\Offline\OfflinePaymentMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;

class TransactionInfo extends Component
{
    use LivewireAlert, WithFileUploads;
    use WithFileUploads;

    public Payment $payment;
    public $plan;
    public $subscription;
    public $images = [];
    public $gateway_subscription_id;
    public $transaction_id;
    public $showImageSection = false;
    public $previewImageUrl;

    protected $rules = [
        'gateway_subscription_id' => 'nullable|string',
        'transaction_id' => 'nullable|string',
        'images.*' => 'image|mimes:jpeg,jpg,png'
    ];

    public function mount(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        $this->payment = $payment;
        $this->plan = $payment->plan;
        $this->subscription = $payment->subscription;

        $this->gateway_subscription_id = $payment->gateway_subscription_id;
        $this->transaction_id = $payment->transaction_id;

        if ($this->subscription) {
            $this->subscription->started_at = $this->subscription->created_at->toDateTimeString();
            $this->subscription->expired_at = $this->subscription->expired_at->toDateTimeString();
            $this->subscription->remaining_time = Carbon::parse($this->subscription->expired_at)->diffForHumans(Carbon::now(), [
                'parts' => 3,
                'join' => true,
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            ]);
        }

        // Check if images should be shown based on payment gateway
        if ($payment->gateway !== 'paypal') {
            $offlineMethod = OfflinePaymentMethod::where('slug', $payment->gateway)->first();
            $this->showImageSection = $offlineMethod ? $offlineMethod->receipt_image : false;
        }
    }

    public function updatePaymentDetails()
    {
        if ($this->payment->gateway === 'paypal') {
            $this->alert('error', 'PayPal transaction details cannot be modified.', [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        $this->validate([
            'gateway_subscription_id' => 'nullable|string',
            'transaction_id' => 'nullable|string',
        ]);

        $this->payment->update([
            'gateway_subscription_id' => $this->gateway_subscription_id,
            'transaction_id' => $this->transaction_id,
        ]);

        $this->alert('success', 'Payment details updated successfully!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function uploadImages()
    {
        if (!$this->showImageSection) {
            return;
        }

        $this->validate([
            'images.*' => 'image|max:2048'
        ]);
        
        $userId = auth()->id();
        foreach ($this->images as $image) {
            $path = $image->store('users/' . $userId . '/payments/' . $this->payment->id, 'public');
            $this->payment->images()->create([
                'image_path' => $path
            ]);
        }

        $this->images = [];
        $this->alert('success', 'Images uploaded successfully!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function deleteImage(PaymentImage $image)
    {
        if (!$this->showImageSection) {
            return;
        }

        $image->delete();
        $this->alert('success', 'Image deleted successfully!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function removeImage($index)
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            $this->images = array_values($this->images);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.subscription.transaction.transaction-info', [
            'paymentImages' => $this->payment->images
        ])->layout('layouts.app', ['title' => 'Transaction Details']);
    }
}
