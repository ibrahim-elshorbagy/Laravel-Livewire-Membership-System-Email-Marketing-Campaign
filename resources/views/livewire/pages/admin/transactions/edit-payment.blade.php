<div class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Payment #{{ $payment->id }}</h2>
        <x-primary-info-button href="{{ route('admin.payment.transactions') }}" wire:navigate>
            Back to Payments
        </x-primary-info-button>
    </div>

    <!-- Customer Info -->
    <div class="mb-6">
        <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Customer</h3>
        <div class="flex gap-2 items-center w-max">
            <img class="object-cover rounded-full size-10" src="{{ $user->image_url ?? 'default-avatar.png' }}"
                alt="{{ $user->first_name }}" />
            <div class="flex flex-col">
                <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }}</span>
                <span class="text-sm text-neutral-500">{{ $user->email }}</span>
            </div>
        </div>
    </div>

    <!-- Plan Info -->
    <div class="mb-6">
        <h3 class="mb-2 text-lg font-semibold text-neutral-700 dark:text-neutral-300">Plan</h3>
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
                    Period: {{ Carbon\Carbon::parse($subscription->started_at)->format('d / m / Y') }}
                    to
                    {{ Carbon\Carbon::parse($subscription->expired_at)->format('d / m / Y') }}
                </span>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    Remaining time: {{ $subscription->remaining_time }}
                </span>
            </div>
        </div>
    </div>
    @endif
    @endif

<!-- Payment Details Form -->
<form wire:submit.prevent="updatePayment" class="rounded-lg bg-neutral-50 dark:bg-neutral-900">
    <h2 class="mb-6 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
        Update Payment Details
    </h2>

    <div class="space-y-6">
        <!-- Transaction Information Section -->
        <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
            <h3 class="mb-4 text-xs font-medium md:text-sm text-neutral-500 dark:text-neutral-400">
                <span class="font-medium">Transaction Information </span>
                <span
                    class="px-2 py-1 ml-2 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-100">
                    {{ $payment->gateway }}
                </span>
            </h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <x-input-label for="gateway_subscription_id" :value="__('Gateway Subscription ID')" />
                    <div class="flex items-center">
                        <x-text-input wire:model="gateway_subscription_id" :readonly="$payment->gateway == 'paypal'" :disabled="$payment->gateway == 'paypal'"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900"  />
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
                    <x-input-label  for="transaction_id" :value="__('Transaction ID')" />
                    <div class="flex items-center">
                        <x-text-input wire:model="transaction_id" :readonly="$payment->gateway == 'paypal'" :disabled="$payment->gateway == 'paypal'"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900"  />
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
        </div>

        <!-- Payment Details Section -->
        <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium text-neutral-500 dark:text-neutral-400">
                Payment Details
            </h3>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Amount Input with Currency -->
                <div class="space-y-2">
                    <x-input-label for="amount" :value="__('Amount')" />
                    <div class="relative">
                        <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                            <span class="text-neutral-500 sm:text-sm">$</span>
                        </div>
                        <x-text-input wire:model="amount" id="amount" type="number" step="0.01"
                            class="block pr-12 pl-7 w-full" />
                        <div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                            <span class="text-neutral-500 sm:text-sm">USD</span>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>

                <!-- Status Select -->
                <div class="space-y-2">
                    <x-input-label for="status" :value="__('Payment Status')" />
                    <x-primary-select-input wire:model="status" id="status" class="w-full">
                        <option>Select Status</option>
                        <option value="pending"> Pending</option>
                        <option value="processing">Processing</option>
                        <option value="approved"> Approved</option>
                        <option value="failed"> Failed</option>
                        <option value="cancelled"> Cancelled</option>
                        <option value="refunded"> Refunded</option>
                    </x-primary-select-input>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>

                <!-- Payment Gateway -->
                <div class="space-y-2">
                    <x-input-label for="gateway" :value="__('Payment Gateway')" />
                    <x-primary-select-input wire:model="gateway" id="gateway" class="w-full">
                        <option value="paypal">PayPal</option>
                        @foreach($offlinePaymentMethods as $method)
                            <option value="{{ $method->slug }}">{{ $method->name }}</option>
                        @endforeach
                    </x-primary-select-input>
                    <x-input-error :messages="$errors->get('gateway')" class="mt-1" />
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end pt-4 mt-6 space-x-3 border-t border-neutral-200 dark:border-neutral-700">
        <x-secondary-button href="{{ route('admin.payment.transactions') }}" wire:navigate class="px-4 py-2">
            Cancel
        </x-secondary-button>
        <x-primary-create-button type="submit" class="px-4 py-2">
            Update Payment
        </x-primary-create-button>
    </div>
</form>

<!-- Payment Images Section -->
@if($payment->images && count($payment->images) > 0)
<div class="p-4 mt-6 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
    <h3 class="mb-4 text-sm font-semibold md:text-lg text-neutral-700 dark:text-neutral-300">Payment Images</h3>

    <!-- Display Existing Images -->
    <div x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach($payment->images as $image)
        <div class="relative group">
            <img src="{{ Storage::url($image->image_path) }}" alt="Payment Image"
                class="object-cover w-full h-full rounded-lg cursor-pointer hover:opacity-90"
                @click="$dispatch('open-modal', 'image-preview-modal'); $wire.previewImageUrl = '{{ Storage::url($image->image_path) }}'" />
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
</div>
@endif

</div>