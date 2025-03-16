<div
    class="flex flex-col p-3 rounded-md border sm:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Header -->
    <div class="mb-4 sm:mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold leading-7 sm:text-2xl sm:truncate">
                Edit Subscription for {{ $subscription->subscriber->first_name }} {{ $subscription->subscriber->last_name }}
            </h2>
        </div>
        <div class="flex mt-2 sm:mt-4 md:mt-0 md:ml-4">
            <x-primary-info-button href="{{ route('admin.subscriptions') }}" wire:navigate>
                Back to Subscriptions
            </x-primary-info-button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        <!-- Plan Management (New Section) -->
        <div class="p-4 rounded-md border sm:p-6 border-neutral-300 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-900 md:text-md dark:text-white">Plan Management</h3>
                <x-primary-create-button type="button" x-data="" x-on:click="$dispatch('open-modal', 'edit-plan')">
                    Switch Plan
                </x-primary-create-button>
            </div>

            <div class="space-y-4">
                <!-- Current Plan Info -->
                <div>
                    <span class="block text-sm font-medium text-gray-700 md:text-md dark:text-gray-300">
                        Current Plan
                    </span>
                    <div
                        class="p-3 mt-2 bg-white rounded-lg border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900 md:text-md dark:text-white">
                                {{ $currentPlan->name }}
                            </span>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                ${{ number_format($currentPlan->price, 2) }}/month
                            </span>
                        </div>
                        @if($currentPlan->description)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $currentPlan->description }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Plan Features -->
                <div>
                    <span class="block text-sm font-medium text-gray-700 md:text-md dark:text-gray-300">
                        Plan Features
                    </span>
                    <div class="mt-2 space-y-2">
                        @foreach($currentPlan->features as $feature)
                        <div class="flex justify-between items-center p-2 rounded">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                - {{ $feature->name }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $feature->pivot->charges ?? 'Unlimited' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Management -->
        <div class="p-3 rounded-md border sm:p-6 border-neutral-300 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-3 sm:mb-4">
                <h3 class="font-semibold text-gray-900 md:text-md dark:text-white">Subscription Management</h3>
                <x-primary-create-button type="button" x-data=""
                    x-on:click="$dispatch('open-modal', 'edit-subscription')">
                    Update
                </x-primary-create-button>
            </div>

            <!-- Subscription Status -->
            <div class="space-y-3 sm:space-y-4">

                <div class="border-t border-neutral-300 dark:border-neutral-700">
                    <span class="block mt-3 text-sm font-medium text-gray-700 md:text-lg dark:text-gray-300">
                        Subscription Status
                    </span>
                    <p class="mt-1">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($subscription->suppressed_at)
                                text-orange-800 bg-orange-100
                            @elseif($subscription->canceled_at)
                                text-red-800 bg-red-100
                            @else
                                text-green-800 bg-green-100
                            @endif
                        ">
                            @if($subscription->suppressed_at)
                            Suppressed
                            @elseif($subscription->canceled_at)
                            Canceled
                            @else
                            Active
                            @endif
                        </span>
                    </p>
                </div>


                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <span class="text-sm md:text-mdtext-neutral-600">Started At</span>
                        <p class="text-sm font-medium sm:text-base">
                            {{ \Carbon\Carbon::parse($started_at)->format('d, M Y') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm md:text-mdtext-neutral-600">Expires At</span>
                        <p class="text-sm font-medium sm:text-base">
                            {{ $expired_at ? \Carbon\Carbon::parse($expired_at)->format('d, M Y') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <div>
                    <span class="text-sm md:text-mdtext-neutral-600">Grace Period End</span>
                    <p class="mb-2 text-xs sm:text-sm text-neutral-500">
                        Set a date until when the subscription will remain active after expiration.
                    </p>
                    <p class="text-sm font-medium sm:text-base">
                        {{ $grace_days_ended_at ? \Carbon\Carbon::parse($grace_days_ended_at)->format('d, M Y H:i') :
                        'N/A' }}
                    </p>
                </div>

                <div class="flex gap-5 items-center py-4 border-t border-neutral-300 dark:border-neutral-700">
                    <span class="block text-sm font-medium text-gray-700 md:text-lg dark:text-gray-300">ServerStatus</span>
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $server_status === 'running' ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}">
                        {{ ucfirst($server_status) }}
                    </span>

                </div>
            </div>

        <!-- Action Buttons -->
        <div class="py-4 space-y-4 border-t border-neutral-300 dark:border-neutral-700">
            <!-- Buttons Grid -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-2">
                <!-- Cancel Button -->
                <div class="w-full">
                    <x-primary-danger-button class="justify-center w-full" wire:click="cancelSubscription"
                        wire:confirm="Are you sure you want to cancel this subscription?">
                        <span class="text-center">Cancel Subscription</span>
                    </x-primary-danger-button>
                </div>

                <!-- Suppress Button -->
                @if(!$subscription->suppressed_at)
                <div class="w-full">
                    <x-primary-danger-button class="justify-center w-full" wire:click="suppressSubscription"
                        wire:confirm="Are you sure you want to suppress this subscription?">
                        <span class="text-center">Suppress Subscription</span>
                    </x-primary-danger-button>
                </div>
                @endif
                <!-- Reactive Button (Conditional) -->
                @if($subscription->suppressed_at || $subscription->canceled_at)
                {{-- <div class="col-span-full w-full sm:col-span-2 lg:col-span-1"> --}}
                <div class="col-span-full w-full">
                    <x-primary-create-button class="justify-center w-full" wire:click="reActiveSubscription"
                        wire:confirm="Are you sure you want to reactivate this subscription?">
                        <span class="text-center">Reactivate Subscription</span>
                    </x-primary-create-button>
                </div>
                @endif
            </div>

            <!-- Warning Message -->
            <div class="p-4 bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
                <div class="flex flex-col sm:flex-row">
                    <!-- Icon -->
                    <div class="flex-shrink-0 sm:mr-3">
                        <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="mt-2 sm:mt-0">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Important Note
                        </h3>
                        <div class="mt-2 space-y-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p class="text-xs sm:text-sm text-neutral-500">
                                - Cancelling will keep access until the expiration date, but there won't be any grace period.
                            </p>
                            <p class="text-xs sm:text-sm text-neutral-500">
                                - Suppressing immediately stops access To all Features.
                            </p>
                            <p class="text-xs sm:text-sm text-neutral-500">
                                - Reactivating will restore access.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>

        <!-- Payment Details -->
        <div class="p-3 rounded-md border sm:p-6 border-neutral-300 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-3 sm:mb-4">
                <h3 class="font-semibold text-gray-900 md:text-md dark:text-white">Payment Details</h3>
                <x-primary-create-button type="button" x-data="" x-on:click="$dispatch('open-modal', 'edit-payment')">
                    Update
                </x-primary-create-button>
            </div>

            <!-- Read-only Payment Info -->
            <div class="space-y-3 sm:space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Payment Gateway</span>
                        <p class="mt-1 text-sm font-medium sm:text-base">{{ ucfirst($payment?->gateway) }}</p>
                    </div>

                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Currency</span>
                        <p class="mt-1 text-sm font-medium sm:text-base">{{ $payment?->currency }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Gateway Subscription ID</span>
                        <p class="mt-1 text-sm font-medium break-all sm:text-base">{{ $payment?->gateway_subscription_id
                            }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Transaction ID</span>
                        <p class="mt-1 text-sm font-medium break-all sm:text-base">{{ $payment?->transaction_id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Amount</span>
                        <p class="mt-1 text-sm font-medium sm:text-base">${{ number_format($payment?->amount, 2) }}</p>
                    </div>

                    <div>
                        <span class="text-xs font-medium sm:text-sm text-neutral-500">Status</span>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @switch($payment?->status)
                                    @case('approved')
                                        text-green-800 bg-green-100
                                        @break
                                    @case('pending')
                                        text-yellow-800 bg-yellow-100
                                        @break
                                    @case('failed')
                                        text-red-800 bg-red-100
                                        @break
                                    @case('cancelled')
                                        text-gray-800 bg-gray-100
                                        @break
                                    @case('refunded')
                                        text-purple-800 bg-purple-100
                                        @break
                                @endswitch
                            ">
                                {{ ucfirst($payment?->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Subscription Modal -->
    <x-modal name="edit-subscription" :show="false">
        <form wire:submit.prevent="updateSubscriptionDetails" class="p-6">
            <h2 class="text-lg font-medium">
                Update Subscription Details
            </h2>

            <div class="mt-6 space-y-4">
                <!-- Start Date -->
                <div>
                    <x-input-label for="started_at" :value="__('Start Date')" />
                    <x-text-input x-data x-init="flatpickr($el, {
                            dateFormat: 'Y-m-d',
                            defaultDate: '{{ $started_at }}',
                            allowInput: true
                        })" wire:model="started_at" type="text" class="block mt-1 w-full" placeholder="YYYY-MM-DD" />
                    <x-input-error :messages="$errors->get('started_at')" class="mt-2" />
                </div>

                <!-- Expiration Date -->
                <div>
                    <x-input-label for="expired_at" :value="__('Expiration Date')" />
                    <x-text-input x-data x-init="flatpickr($el, {
                            dateFormat: 'Y-m-d',
                            defaultDate: '{{ $expired_at }}',
                            allowInput: true
                        })" wire:model="expired_at" type="text" class="block mt-1 w-full" placeholder="YYYY-MM-DD" />
                    <x-input-error :messages="$errors->get('expired_at')" class="mt-2" />
                </div>

                <!-- Grace Period -->
                <div>
                    <x-input-label for="grace_days_ended_at" :value="__('Grace Period End')" />
                    <x-text-input x-data x-init="flatpickr($el, {
                            dateFormat: 'Y-m-d H:i',
                            enableTime: true,
                            time_24hr: true,
                            defaultDate: '{{ $grace_days_ended_at }}',
                            allowInput: true
                        })" wire:model="grace_days_ended_at" type="text" class="block mt-1 w-full" placeholder="YYYY-MM-DD HH:MM" />
                    <x-input-error :messages="$errors->get('grace_days_ended_at')" class="mt-2" />
                </div>
                <!-- Server Status -->
                <div>
                    <x-input-label for="server_status" :value="__('Server Status')" />
                    <x-primary-select-input wire:model="server_status" id="server_status">
                        <option value="running">Running</option>
                        <option value="hold">Hold</option>
                    </x-primary-select-input>
                    <x-input-error :messages="$errors->get('server_status')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>

                <x-primary-create-button type="submit">
                    Update Subscription
                </x-primary-create-button>
            </div>
        </form>
    </x-modal>

    <!-- Edit Payment Modal -->
    <x-modal name="edit-payment" :show="false">
        <form wire:submit.prevent="updatePayment" class="p-6">
            <h2 class="text-lg font-medium">
                Update Payment Details
            </h2>

            <div class="mt-6 space-y-4">
                <!-- Amount -->
                <div>
                    <x-input-label for="amount" :value="__('Amount')" />
                    <x-text-input wire:model="amount" id="amount" type="number" step="0.01" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <!-- Status -->
                <div>
                    <x-input-label for="status" :value="__('Payment Status')" />
                    <x-primary-select-input wire:model="status" id="status">
                        <option>Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="processing">Processing</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </x-primary-select-input>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>

                <x-primary-create-button type="submit">
                    Update Payment
                </x-primary-create-button>
            </div>
        </form>
    </x-modal>

    <!-- Add new Plan Switch Modal -->
    <x-modal name="edit-plan" :show="false">
        <form wire:submit.prevent="switchPlan" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Switch Subscription Plan
            </h2>

            <div class="mt-6 space-y-4">
                <div>
                    <x-input-label for="selectedPlan" :value="__('Select New Plan')" />
                    <div class="mt-2 space-y-3">
                        @foreach($availablePlans as $plan)
                        <label
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800
                                    {{ $selectedPlan == $plan->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                            <input type="radio" wire:model="selectedPlan" value="{{ $plan->id }}"
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="flex-1 ml-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $plan->name }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        ${{ number_format($plan->price, 2) }}/month
                                    </span>
                                </div>
                                @if($plan->description)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $plan->description }}
                                </p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('selectedPlan')" class="mt-2" />
                </div>

                <!-- Warning Message -->
                <div class="p-4 mt-4 bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Important Note
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>
                                    Switching plans will take effect immediately. The subscriber's features and limits
                                    will
                                    be updated according to the new plan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>

                <x-primary-create-button type="submit">
                    Confirm Plan Switch
                </x-primary-create-button>
            </div>
        </form>
    </x-modal>

</div>
