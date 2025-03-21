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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Validator;


class PaymentMethodSelection extends Component
{
    use LivewireAlert, WithFileUploads;
    use PlanPriceCalculator;

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
        // Validate using Laravel's Validator
        $validator = Validator::make(
            ['selectedPlan' => $selectedPlan],
            ['selectedPlan' => 'required|exists:plans,id']
        );

        if ($validator->fails()) {
            $this->alert('error', 'Please select a valid plan.');
            return;
        }

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
            $this->upgradeCalculation = $this->calculateUpgradeCost($this->selectedPlan);
            $PaymentCalculation = $this->upgradeCalculation['upgrade_cost'];

            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'plan_id' => $this->selectedPlan,
                'gateway' => $this->selectedMethod,
                'amount' => $PaymentCalculation,
                'currency' => 'USD',
                'status' => 'pending',
            ]);

            $userId = $user->id;
            $manager = new ImageManager(new Driver());

            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    try {
                        // Read the uploaded image
                        $img = $manager->read($image);

                        // Generate a unique filename
                        $fileName = 'payment_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                        // Define the storage path
                        $storagePath = 'users/' . $userId . '/payments/' . $payment->id;
                        $fullPath = Storage::disk('public')->path($storagePath);

                        // Ensure the directory exists
                        if (!File::exists($fullPath)) {
                            File::makeDirectory($fullPath, 0755, true, true);
                        }

                        // Full path for saving
                        $fullFilePath = $fullPath . '/' . $fileName;
                        $savedPath = $storagePath . '/' . $fileName;

                        // Save the image with compression
                        $img->save($fullFilePath, [
                            'quality' => 80,
                            'optimize' => true
                        ]);

                        // Create image record
                        $payment->images()->create([
                            'image_path' => $savedPath
                        ]);


                    } catch (\Exception $e) {
                        $this->alert('error', 'Failed to upload an image: ' . $e->getMessage(), [
                            'position' => 'bottom-end',
                            'timer' => 3000,
                            'toast' => true,
                        ]);

                        continue;
                    }
                }
            }

            DB::commit();

            $this->alert('success', 'Payment information submitted successfully!');
            $this->redirect(route('user.my-transactions'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to process payment: ' . $e->getMessage());

            // Optional: Log the full error
            Log::error('Payment Processing Error: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.pages.user.subscription.payment-method-selection');
    }
}
