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
    @if($subscription)
    <div class="mb-6">
        <h3 class="mb-2 text-lg font-semibold text-neutral-700 dark:text-neutral-300">Subscription</h3>
        <div class="p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800">
            <div class="flex flex-col gap-2">
                <span class="text-sm text-neutral-600 dark:text-neutral-400">
                    Period: {{ Carbon\Carbon::parse($subscription->started_at)->timezone(auth()->user()->timezone ??
                    SiteSetting::getValue('APP_TIMEZONE'))->format('d/m/Y h:i A')}}
                    to
                    {{ Carbon\Carbon::parse($subscription->expired_at)->timezone(auth()->user()->timezone ??
                    SiteSetting::getValue('APP_TIMEZONE'))->format('d/m/Y h:i A')}}
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

    <!-- Payment Details Form -->
    <form wire:submit.prevent="updatePaymentDetails" class="mb-6">
        <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
            <h3
                class="flex flex-col gap-2 items-center mb-4 text-xs font-medium md:text-sm text-neutral-500 dark:text-neutral-400 md:flex-row w-fit">
                <span class="text-sm font-semibold md:text-lg text-neutral-700 dark:text-neutral-300">Transaction
                    Information </span>
                <span
                    class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-100">
                    {{ $payment->gateway }}
                </span>
            </h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <x-input-label for="gateway_subscription_id"
                        :value="$payment->gateway == 'paypal' ? __('Gateway Order ID') : __('Note')" />
                    <div class="flex items-center">
                        @if($payment->gateway == 'paypal')
                        <x-text-input wire:model="gateway_subscription_id" :readonly="true" :disabled="true"
                            placeholder="Gateway Order ID" class="block w-full bg-neutral-50 dark:bg-neutral-900" />
                        @else
                        <x-primary-textarea wire:model="gateway_subscription_id" placeholder="Note"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900" />
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('gateway_subscription_id')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="transaction_id"
                        :value="$payment->gateway == 'paypal' ? __('Transaction ID') : __('Transfer record # and details (MTCN)')" />
                    <div class="flex items-center">
                        <x-text-input wire:model="transaction_id" :readonly="$payment->gateway == 'paypal'"
                            placeholder="Transfer record # and details (MTCN)" :disabled="$payment->gateway == 'paypal'"
                            class="block w-full bg-neutral-50 dark:bg-neutral-900" />
                    </div>
                    <x-input-error :messages="$errors->get('transaction_id')" class="mt-2" />
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

    <!-- Payment Files Section -->
    @if($showFileSection)
    <div class="p-4 bg-white rounded-lg border dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
        <h3 class="mb-4 text-sm font-semibold md:text-lg text-neutral-700 dark:text-neutral-300">Payment Files</h3>

        <!-- File Upload Form -->
        <form wire:submit.prevent="uploadFiles" class="mb-6">
            <div class="space-y-4">
                <div class="flex justify-center items-center w-full">
                    <label for="files"
                        class="flex flex-col justify-center items-center w-full h-32 rounded-lg border-2 border-dashed cursor-pointer border-neutral-300 bg-neutral-50 dark:hover:bg-neutral-800 dark:bg-neutral-700 hover:bg-neutral-100 dark:border-neutral-600 dark:hover:border-neutral-500">
                        <div class="flex flex-col justify-center items-center pt-5 pb-6">
                            <i
                                class="mb-4 w-8 h-8 text-2xl text-neutral-500 dark:text-neutral-400 fas fa-cloud-upload-alt"></i>
                            <p class="mb-2 text-sm text-neutral-500 dark:text-neutral-400">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG, JPEG or PDF</p>
                        </div>
                        <input id="files" type="file" wire:model="files" class="hidden" multiple
                            accept="image/png,image/jpg,image/jpeg,application/pdf" />
                    </label>
                </div>
                @error('files.*')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-center items-center w-full">
                    <div wire:loading wire:target="files" class="flex flex-col justify-center items-center py-4">
                        <i class="mb-2 text-2xl text-blue-500 fas fa-spinner fa-spin"></i>
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Uploading files...</span>
                    </div>
                </div>

                @if($files)
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach($files as $key => $file)
                    <div class="relative group">
                        @php
                        $extension = strtolower($file->getClientOriginalExtension());
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'jfif']);
                        @endphp
                        @if($isImage)
                        <img src="{{ $file->temporaryUrl() }}" alt="Payment File"
                            class="object-cover w-full h-full rounded-lg cursor-pointer hover:opacity-90"
                            @click="$dispatch('open-modal', 'file-preview-modal'); $wire.previewUrl = '{{ $file->temporaryUrl() }}'; $wire.previewType = 'image'" />
                        @else
                        <a href="{{ $file->temporaryUrl() }}" target="_blank" rel="noopener noreferrer" class="block">
                            <div
                                class="flex flex-col justify-center items-center p-4 w-full h-full min-h-[200px] rounded-lg hover:opacity-90 bg-neutral-100 dark:bg-neutral-800">
                                <i class="mb-2 text-4xl text-neutral-500 dark:text-neutral-400 fas fa-file-pdf"></i>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{
                                    $file->getClientOriginalName() }}</span>
                            </div>
                        </a>
                        @endif
                        <button type="button" wire:click="removeFile({{ $key }})"
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
                        Upload Files
                    </x-primary-create-button>
                </div>
            </div>
        </form>

        <!-- Display Existing Files -->
        @if($paymentFiles && count($paymentFiles) > 0)
        <div x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach($paymentFiles as $file)
            <div class="relative group">
                @php
                $extension = strtolower(pathinfo($file->image_path, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'jfif']);
                @endphp
                @if($isImage)
                <img src="{{ Storage::url($file->image_path) }}" alt="Payment File"
                    class="object-cover w-full h-full rounded-lg cursor-pointer hover:opacity-90"
                    @click="$dispatch('open-modal', 'file-preview-modal'); $wire.previewUrl = '{{ Storage::url($file->image_path) }}'; $wire.previewType = 'image'" />
                @else
                <a href="{{ Storage::url($file->image_path) }}" target="_blank" rel="noopener noreferrer" class="block">
                    <div
                        class="flex flex-col justify-center items-center p-4 w-full h-full min-h-[200px] rounded-lg hover:opacity-90 bg-neutral-100 dark:bg-neutral-800">
                        <i class="mb-2 text-4xl text-neutral-500 dark:text-neutral-400 fas fa-file-pdf"></i>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ basename($file->image_path) }}</span>
                        <a href="{{ Storage::url($file->image_path) }}" target="_blank" rel="noopener noreferrer" class="mt-2 text-sm text-blue-500 hover:text-blue-700">
                            <i class="mr-1 fas fa-download"></i>Download
                        </a>
                    </div>
                </a>
                @endif
                <button wire:click="deleteFile({{ $file->id }})"
                    wire:confirm="Are you sure you want to delete this file?"
                    class="absolute top-2 right-2 p-1 text-white bg-red-500 rounded-full opacity-0 group-hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endforeach
        </div>

        <!-- File Preview Modal -->
        <x-modal name="file-preview-modal" maxWidth="4xl">
            <div class="relative p-2">

                @if($previewType === 'image')
                <img :src="$wire.previewUrl" alt="Preview Image" class="w-full h-auto rounded-lg" />
                @else
                <iframe :src="$wire.previewUrl" class="w-full h-[80vh] rounded-lg" frameborder="0"></iframe>
                @endif
            </div>
        </x-modal>
        @endif
    </div>
    @endif
</div>