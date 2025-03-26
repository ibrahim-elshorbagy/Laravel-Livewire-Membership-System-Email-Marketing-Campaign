<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Header -->
    <div class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Transaction </h2>
        <x-primary-info-button href="{{ route('user.my-transactions') }}" wire:navigate>
            Back to Transactions
        </x-primary-info-button>
    </div>

    <!-- Plan Info -->
    <div class="mb-6">
        <h3 class="mb-2 font-semibold text-md md:text-lg text-neutral-700 dark:text-neutral-300">Plan</h3>
        <div class="p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800">
            <span class="font-medium">{{ $plan->name }}</span>
            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                - ${{ number_format($plan->price, 2) }} USD
            </span>
        </div>
    </div>

    <!-- Subscription Info -->
    @if($plan->id != 1)
    @if($subscription)
    <div class="mb-6">
        <h3 class="mb-2 text-lg font-semibold text-neutral-700 dark:text-neutral-300">Subscription</h3>
        <div class="p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800">
            <div class="flex flex-col gap-2">
                <span class="text-sm text-neutral-600 dark:text-neutral-400">
                    Period: {{ Carbon\Carbon::parse($subscription->started_at)->timezone(auth()->user()->timezone ?? SiteSetting::getValue('APP_TIMEZONE'))->format('d/m/Y h:i A')}}
                    to
                    {{ Carbon\Carbon::parse($subscription->expired_at)->timezone(auth()->user()->timezone ?? SiteSetting::getValue('APP_TIMEZONE'))->format('d/m/Y h:i A')}}
                </span>
                @empty($subscription->suppressed_at)
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    Remaining time: {{ $subscription->remaining_time }}
                </span>
                @endempty
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Payment Details Form -->
    <form wire:submit.prevent="updatePaymentDetails" class="mb-6">
        <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
            <h3 class="flex flex-col gap-2 items-center mb-4 text-xs font-medium md:text-sm text-neutral-500 dark:text-neutral-400 md:flex-row w-fit">
                <span class="text-sm font-semibold md:text-lg text-neutral-700 dark:text-neutral-300">Transaction Information </span>
                <span
                    class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-100">
                    {{ $payment->gateway }}
                </span>
            </h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <x-input-label for="gateway_subscription_id" :value="__('Gateway Subscription ID')" />
                    <div class="flex items-center">
                        <x-text-input wire:model="gateway_subscription_id" :readonly="$payment->gateway == 'paypal'"
                            :disabled="$payment->gateway == 'paypal'"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900" />
                        <button type="button"
                            class="ml-2 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                            onclick="navigator.clipboard.writeText('{{ $payment->gateway_subscription_id }}')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <x-input-label for="transaction_id" :value="__('Transaction ID')" />
                    <div class="flex items-center">
                        <x-text-input wire:model="transaction_id" :readonly="$payment->gateway == 'paypal'"
                            :disabled="$payment->gateway == 'paypal'"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900" />
                        <button type="button"
                            class="ml-2 text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                            onclick="navigator.clipboard.writeText('{{ $payment->transaction_id }}')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end pt-4 mt-4 space-x-3 border-t border-neutral-200 dark:border-neutral-700">
                <x-primary-create-button type="submit" class="px-4 py-2">
                    Update Details
                </x-primary-create-button>
            </div>
        </div>
    </form>

    <!-- Payment Images Section -->
    @if($showImageSection)
    <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
        <h3 class="mb-4 text-sm font-semibold md:text-lg text-neutral-700 dark:text-neutral-300">Payment Images</h3>

        <!-- Image Upload Form -->
        <form wire:submit.prevent="uploadImages" class="mb-6">
            <div class="space-y-4">
                <div class="flex justify-center items-center w-full">
                    <label for="images"
                        class="flex flex-col justify-center items-center w-full h-32 rounded-lg border-2 border-dashed cursor-pointer border-neutral-300 bg-neutral-50 dark:hover:bg-neutral-800 dark:bg-neutral-700 hover:bg-neutral-100 dark:border-neutral-600 dark:hover:border-neutral-500">
                        <div class="flex flex-col justify-center items-center pt-5 pb-6">
                            <i
                                class="mb-4 w-8 h-8 text-2xl text-neutral-500 dark:text-neutral-400 fas fa-cloud-upload-alt"></i>
                            <p class="mb-2 text-sm text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG or JPEG</p>
                        </div>
                        <input id="images" type="file" wire:model="images" class="hidden" multiple
                            accept="image/png,image/jpg,image/jpeg" />
                    </label>
                </div>
                @error('images.*')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-center items-center w-full">
                    <div wire:loading wire:target="images" class="flex flex-col justify-center items-center py-4">
                        <i class="mb-2 text-2xl text-blue-500 fas fa-spinner fa-spin"></i>
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Uploading images...</span>
                    </div>
                </div>

                @if($images)
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach($images as $key => $image)
                    <div class="relative group">
                        <img src="{{ $image->temporaryUrl() }}" alt="Payment Image"
                            class="object-cover w-full h-full rounded-lg cursor-pointer hover:opacity-90"
                            @click="$dispatch('open-modal', 'image-preview-modal'); $wire.previewImageUrl = '{{ $image->temporaryUrl()}}'"/>
                        <button type="button" wire:click="removeImage({{ $key }})"
                            class="absolute top-2 right-2 p-1 text-white bg-red-500 rounded-full opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="flex justify-end">
                    <x-primary-create-button type="submit" class="px-4 py-2">
                        Upload Images
                    </x-primary-create-button>
                </div>
            </div>
        </form>

        <!-- Display Existing Images -->
        @if($paymentImages && count($paymentImages) > 0)
        <div x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach($paymentImages as $image)
            <div class="relative group">
                <img src="{{ Storage::url($image->image_path) }}" alt="Payment Image"
                    class="object-cover w-full h-full rounded-lg cursor-pointer hover:opacity-90"
                    @click="$dispatch('open-modal', 'image-preview-modal'); $wire.previewImageUrl = '{{ Storage::url($image->image_path) }}'" />
                <button wire:click="deleteImage({{ $image->id }})"
                    wire:confirm="Are you sure you want to delete this image?"
                    class="absolute top-2 right-2 p-1 text-white bg-red-500 rounded-full opacity-0 group-hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endforeach
        </div>

        <!-- Image Preview Modal -->
        <x-modal name="image-preview-modal" maxWidth="4xl">
            <div class="relative p-2">
                <button type="button" @click="$dispatch('close-modal', 'image-preview-modal')"
                    class="absolute top-2 right-2 p-1 text-white rounded-full bg-neutral-800 hover:bg-neutral-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="$wire.previewImageUrl" alt="Preview Image" class="w-full h-auto rounded-lg" />
            </div>
        </x-modal>
        @endif
    </div>
    @endif
</div>
