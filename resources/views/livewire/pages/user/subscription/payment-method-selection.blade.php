<div x-data="{ offlineMethods: {{ $offlineMethods }} ?? [] }">

    <x-modal name="payment-method-modal" maxWidth="4xl">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">Select Payment Method</h2>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    Choose your preferred payment method to complete the subscription
                </p>
            </div>

            <!-- Payment Methods -->
            <div class="space-y-4">
                <!-- PayPal Option -->
                <label x-bind:class="{
                        'relative flex p-4 cursor-pointer rounded-lg border hover:border-black dark:hover:border-orange-500': true,
                        'border-black dark:border-orange-500': $wire.selectedMethod === 'paypal',
                        'border-neutral-200 dark:border-neutral-700': $wire.selectedMethod != 'paypal'
                    }">
                    <div class="flex justify-between items-center w-full">
                        <div class="flex items-center">
                            <input type="radio" wire:model="selectedMethod" x-on:click="$wire.selectedMethod = 'paypal'"
                                value="paypal" class="hidden">
                            <div class="flex-shrink-0">
                                <i class="text-blue-500 fa-brands fa-paypal fa-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium md:text-md text-neutral-900 dark:text-neutral-100">PayPal
                                </h3>
                                <p class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400">Pay securely with
                                    PayPal</p>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div x-bind:class="{
                                'text-black dark:text-orange-500': $wire.selectedMethod === 'paypal',
                                'text-neutral-400': $wire.selectedMethod != 'paypal'
                            }">
                                <i class="w-5 h-5 fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Offline Payment Methods -->
                @foreach($offlineMethods as $method)
                <label x-bind:class="{
                        'relative flex p-4 cursor-pointer rounded-lg border hover:border-black dark:hover:border-orange-500': true,
                        'border-black dark:border-orange-500': $wire.selectedMethod === '{{ $method->slug }}',
                        'border-neutral-200 dark:border-neutral-700': $wire.selectedMethod != '{{ $method->slug }}'
                    }">
                    <div class="flex justify-between items-center w-full">
                        <div class="flex items-center">
                            <input type="radio" wire:model="selectedMethod"
                                x-on:click="$wire.selectedMethod = '{{ $method->slug }}'" value="{{ $method->slug }}"
                                class="hidden">
                            <div class="flex-shrink-0">
                                @if($method->logo)
                                <img src="{{ Storage::url($method->logo) }}" alt="{{ $method->name }} Logo"
                                    class="object-cover w-32 h-32">
                                @else
                                <div
                                    class="flex justify-center items-center w-10 h-10 rounded-full bg-neutral-200 dark:bg-neutral-700">
                                    <i class="fas fa-credit-card text-neutral-400"></i>
                                </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium md:text-md text-neutral-900 dark:text-neutral-100">{{
                                    $method->name }}</h3>
                                <p class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400">Manual bank
                                    transfer</p>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div x-bind:class="{
                                'text-black dark:text-orange-500': $wire.selectedMethod === '{{ $method->slug }}',
                                'text-neutral-400': $wire.selectedMethod != '{{ $method->slug }}'
                            }">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach

                @error('selectedMethod')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Instructions -->
            <div x-show="$wire.selectedMethod" class="p-4 mt-6 rounded-lg bg-neutral-50 dark:bg-neutral-800">
                <h4 class="text-sm font-medium md:text-md text-neutral-900 dark:text-neutral-100">Payment Instructions
                </h4>
                <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 no-tailwindcss-support-display"
                    x-html="$wire.selectedMethod === 'paypal' ? 'Follow PayPal instructions to complete your payment securely.' : offlineMethods.find(m => m.slug === $wire.selectedMethod)?.instructions || ''">
                </div>
            </div>

            <!-- Image Upload Section -->
            <div x-show="$wire.selectedMethod && $wire.selectedMethod != 'paypal' && offlineMethods.find(m => m.slug === $wire.selectedMethod)?.receipt_image"
                class="mt-6">
                <h4 class="mb-4 text-sm font-medium md:text-md text-neutral-900 dark:text-neutral-100">Upload Payment
                    Receipt</h4>
                <p class="mb-4 text-sm text-neutral-500 dark:text-neutral-400">You can attach a photo/pdf of the payment/transfer notification here now, or later on the transactions page.</p>



                <div class="space-y-4">
                    <div class="flex justify-center items-center w-full">
                        <label x-data="{ dragOver: false }" x-on:dragover.prevent="dragOver = true"
                            x-on:dragleave.prevent="dragOver = false"
                            x-on:drop.prevent="dragOver = false; const files = $event.dataTransfer.files; if (files.length) { const input = document.getElementById('images'); const dataTransfer = new DataTransfer(); Array.from(files).forEach(file => dataTransfer.items.add(file)); input.files = dataTransfer.files; const event = new Event('change', { bubbles: true }); input.dispatchEvent(event); }"
                            class="flex flex-col justify-center items-center w-full h-32 rounded-lg border-2 border-dashed transition-colors duration-200 cursor-pointer"
                            :class="{
                                'border-neutral-300 bg-neutral-50 dark:hover:bg-neutral-800 dark:bg-neutral-700 hover:bg-neutral-100 dark:border-neutral-600 dark:hover:border-neutral-500': !dragOver,
                                'border-blue-500 bg-blue-50 dark:bg-blue-900/20': dragOver
                            }">
                            <div class="flex flex-col justify-center items-center pt-5 pb-6">
                                <i
                                    class="mb-4 w-8 h-8 text-2xl text-neutral-500 dark:text-neutral-400 fas fa-cloud-upload-alt"></i>
                                <p class="mb-2 text-xs md:text-sm text-neutral-500 dark:text-neutral-400">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG, JPEG,JFIF or PDF
                                    (MAX. 10MB)</p>
                            </div>
                            <input id="images" type="file" wire:model="images" class="hidden" multiple
                                accept="image/png,image/jpg,image/jpeg,application/pdf" />
                        </label>
                    </div>

                    @error('images.*')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-center items-center w-full">
                        <div wire:loading wire:target="images" class="flex flex-col justify-center items-center py-4">
                            <i class="mb-2 text-2xl text-blue-500 fas fa-spinner fa-spin"></i>
                            <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Uploading files...</span>
                        </div>
                    </div>

                    <!-- Preview Files -->
                    @if($images)
                    <div class="grid gap-4">
                        @foreach($images as $key => $image)
                        <div class="relative group">
                            @if($fileTypes[$key] === 'image')
                            <img src="{{ $image->temporaryUrl() }}" alt="Payment Receipt"
                                class="object-cover w-full cursor-pointer md:h-full md:rounded-lg hover:opacity-90"
                                @click="$dispatch('open-modal', 'image-preview-modal'); $wire.previewImageUrl = '{{ $image->temporaryUrl() }}'" />
                            @else
                            <div
                                class="flex flex-col justify-center items-center p-4 w-full h-full min-h-[200px] rounded-lg hover:opacity-90 bg-neutral-100 dark:bg-neutral-800">
                                <i class="mb-2 text-4xl text-neutral-500 dark:text-neutral-400 fas fa-file-pdf"></i>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{
                                    $image->getClientOriginalName() }}</span>
                                <a href="{{ $image->temporaryUrl() }}" target="_blank" rel="noopener noreferrer"
                                    class="mt-2 text-sm text-blue-500 hover:text-blue-700">
                                    <i class="mr-1 fas fa-download"></i>Download
                                </a>
                            </div>
                            @endif
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
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end mt-6 space-x-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'payment-method-modal');"
                    class="px-2 py-1 text-xs font-medium bg-white rounded-lg border md:text-sm md:py-2 md:px-4 text-neutral-700 border-neutral-300 hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-300 dark:border-neutral-600 dark:hover:bg-neutral-700">
                    Cancel
                </button>
                <x-primary-create-button type="button" wire:click="processPayment" wire:loading.attr="disabled">
                    <span wire:loading.remove>Continue with Payment</span>
                    <span wire:loading>Processing...</span>
                </x-primary-create-button>
            </div>
        </div>
    </x-modal>

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
