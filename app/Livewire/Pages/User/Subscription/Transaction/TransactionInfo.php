<?php

namespace App\Livewire\Pages\User\Subscription\Transaction;

use App\Models\Payment\Payment;
use App\Models\Payment\PaymentImage;
use App\Models\Payment\Offline\OfflinePaymentMethod;
use App\Traits\PlanPriceCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Subscription;
use App\Models\Admin\Site\SiteSetting;

class TransactionInfo extends Component
{
    use LivewireAlert, WithFileUploads;
    use PlanPriceCalculator;


    public Payment $payment;
    public $plan;
    public $subscription;
    public $files = [];
    public $gateway_subscription_id;
    public $transaction_id;
    public $showFileSection = false;
    public $previewUrl;
    public $previewType;
    public $calculatedDates;
    public $time_zone;

    protected $rules = [
        'gateway_subscription_id' => 'nullable|string|max:255',
        'transaction_id' => 'nullable|string|max:255',
        'files.*' => 'file|mimes:jpeg,jfif,jpg,png,pdf|max:10240'
    ];

    protected $messages = [
        'gateway_subscription_id.max' => 'field must not be greater than 255 characters',
        'transaction_id.max' => 'field must not be greater than 255 characters',
        'files.*.max' => 'File must not be larger than 10MB',
        'files.*.mimes' => 'File must be a valid image (JPG, JPEG, PNG) or PDF document'
    ];


    public function mount(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        $this->time_zone = auth()->user()->timezone ?? config('app.timezone');

        $this->payment = $payment;
        $this->plan = $payment->plan;
        $this->subscription = $payment->subscription;
        if($payment->subscription_id){
            $this->subscription = Subscription::with(['plan'])->withoutGlobalScopes([SuppressingScope::class, StartingScope::class])
            ->find($payment->subscription_id);
        }

        $this->calculateDates();

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

        if ($payment->gateway !== 'paypal') {
            $offlineMethod = OfflinePaymentMethod::where('slug', $payment->gateway)->first();
            $this->showFileSection = $offlineMethod ? $offlineMethod->receipt_image : false;
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
            'gateway_subscription_id' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
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

    public function uploadFiles()
    {
        if (!$this->showFileSection) {
            return;
        }

        $this->validate([
            'files.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240'
        ]);

        $userId = auth()->id();
        $manager = new ImageManager(new Driver());

        foreach ($this->files as $file) {
            try {
                $extension = $file->getClientOriginalExtension();
                $fileName = 'payment_' . Str::random(10) . '.' . $extension;
                $storagePath = 'users/' . $userId . '/payments/' . $this->payment->id;
                $fullPath = Storage::disk('public')->path($storagePath);

                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true, true);
                }

                $fullFilePath = $fullPath . '/' . $fileName;
                $savedPath = $storagePath . '/' . $fileName;

                if (in_array($extension, ['jpg', 'jpeg', 'png','jfif'])) {
                    $img = $manager->read($file);
                    $img->save($fullFilePath, [
                        'quality' => 80,
                        'optimize' => true
                    ]);
                } else {
                    $file->storeAs($storagePath, $fileName, 'public');
                }

                $this->payment->images()->create([
                    'image_path' => $savedPath
                ]);

            } catch (\Exception $e) {
                $this->alert('error', 'Failed to upload file: ' . $e->getMessage(), [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                continue;
            }
        }

        $this->files = [];

        $this->alert('success', 'Files uploaded successfully!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function deleteFile(PaymentImage $image)
    {
        if (!$this->showFileSection) {
            return;
        }

        // Delete the physical file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();
        $this->alert('success', 'File deleted successfully!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            unset($this->files[$index]);
            $this->files = array_values($this->files);
        }
    }

    public function getFileType($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png','jfif']) ? 'image' : 'pdf';
    }

    public function calculateDates()
    {
        if (auth()->user()->lastSubscription()) {
            $startDate = Carbon::parse(auth()->user()->lastSubscription()->started_at);
            $endDate = Carbon::parse(auth()->user()->lastSubscription()->expired_at);
            $this->calculatedDates = $this->calculateSubscriptionDates(
                $this->plan,
                auth()->user()->lastSubscription()->plan,
                $startDate,
                $endDate,
                $this->payment->amount
            );
        } else {
            $this->calculatedDates = [
                'will_started_at' => now(),
                'will_expired_at' => $this->plan->periodicity_type === 'Year' ? now()->addYear() : now()->addMonth()
            ];
        }
    }

    public function render()
    {
        return view('livewire.pages.user.subscription.transaction.transaction-info', [
            'paymentFiles' => $this->payment->images
        ])->layout('layouts.app', ['title' => 'Transaction Details']);
    }
}
