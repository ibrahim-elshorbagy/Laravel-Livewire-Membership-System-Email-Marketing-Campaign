@props([
'items',
'search',
'searchPlaceholder' => 'Search...'
])

<div>
    <!-- Search Box -->
    <div class="flex justify-between items-center mb-4">
        <div class="w-full">
            <div class="relative">
                <x-text-input wire:model.live.debounce.600ms="{{ $search }}" id="{{ $search }}" type="text"
                    class="py-2 pr-20 pl-10 w-full" placeholder="{{ $searchPlaceholder }}" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
                @if($$search)
                <div class="flex absolute inset-y-0 right-0 items-center pr-3">
                    <button wire:click="$set('{{ $search }}', '')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div x-text="selectedTab" class="mb-4"></div>
    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-xs text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-xs bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th class="p-4">Subscriber</th>
                    <th class="p-4">Plan</th>
                    <th class="p-4">Limits</th>
                    <th class="p-4">Start Date</th>
                    <th class="p-4">Expiration</th>
                    <th class="p-4 text-nowrap">Payment Status</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($items as $subscription)
                @php
                $subscriber = $subscription->subscriber;
                $payment = $this->getSubscriptionPayment($subscription->id);
                @endphp
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <div class="flex flex-col">
                            <span class="font-medium">
                                Name :
                                {{ $subscriber->first_name }} {{ $subscriber->last_name}}
                                @if($subscriber->deleted_at)
                                <span x-show="selectedTab === 'all'" class="text-xs text-red-500">(Soft Delete)</span>
                                @endif
                            </span>
                            <span>
                                UserName: {{ $subscriber->username }}
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full text-nowrap">
                            {{ $subscription->plan->name }}
                        </span>
                    </td>
                    <td class="p-4 text-nowrap">
                        <div class="flex flex-col gap-2">


                            <!-- Subscribers Limit -->
                            @php
                            $subscribersLimit = $this->getFeatureDetails($subscription, 'Subscribers Limit');
                            @endphp
                                <div class="flex flex-col gap-2 justify-between items-center mb-1">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Subscribers</span>
                                    @if($subscription->suppressed_at)
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                        Suppressed
                                    </span>
                                    @else
                                    <span class="text-xs font-medium text-gray-900 dark:text-gray-200">
                                        {{ $subscribersLimit['remaining'] }} / {{ $subscribersLimit['total'] }}
                                    </span>
                                    @endif
                                </div>

                            <!-- Email Sending Limit -->
                            @php
                            $emailLimit = $this->getFeatureDetails($subscription, 'Email Sending');
                            @endphp
                            <div class="flex flex-col gap-2 justify-between items-center mb-1">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Email Sending</span>
                                @if($subscription->suppressed_at)
                                <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                    Suppressed
                                </span>
                                @else
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-200">
                                    {{ $emailLimit['remaining'] }} / {{ $emailLimit['total'] }}
                                </span>
                                @endif
                            </div>

                        </div>
                    </td>
                    <td class="p-4">{{ $subscription->started_at->format('d/m/Y') }}</td>
                    <td class="p-4">{{ $subscription->expired_at?->format('d/m/Y') }}</td>
                    <td class="p-4">
                        @if($payment)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-nowrap
                            @switch($payment->status)
                                @case('approved') text-green-800 bg-green-100 @break
                                @case('pending') text-yellow-800 bg-yellow-100 @break
                                @case('failed') text-red-800 bg-red-100 @break
                                @default text-gray-800 bg-gray-100
                            @endswitch">
                            <span class="text-nowrap">{{ ucfirst($payment->status) }}</span>

                        </span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full text-nowrap">
                            No Payment
                        </span>
                        @endif
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-nowrap
                            @if($subscription->suppressed_at) text-purple-800 bg-purple-100
                            @elseif($subscription->canceled_at) text-red-800 bg-red-100
                            @elseif($subscription->expired_at?->isPast()) text-orange-800 bg-orange-100
                            @else text-green-800 bg-green-100 @endif">
                            @if($subscription->suppressed_at) Suppressed
                            @elseif($subscription->canceled_at) Canceled
                            @elseif($subscription->expired_at?->isPast()) Expired
                            @else Active @endif
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <x-primary-info-button href="{{ route('admin.subscriptions.edit', $subscription) }}"
                                wire:navigate>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-primary-info-button>
                            <x-primary-info-button href="{{ route('admin.users.transactions', $subscriber) }}" wire:navigate>
                                Transactions
                            </x-primary-info-button>
                            @if(!$subscriber->deleted_at)
                            <x-primary-info-button
                                onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $subscriber->id }})">
                                Login
                            </x-primary-info-button>
                            @endif
                            <x-primary-info-button x-on:click="$dispatch('open-modal', 'subscription-note-{{ $subscription->id }}')">
                                <i class="fa-solid fa-note-sticky"></i>
                            </x-primary-info-button>


                        </div>
                    </td>
                </tr>
                <!-- Note Modal -->
                    <x-modal name="subscription-note-{{ $subscription->id }}" :show="false" :maxWidth="'2xl'">
                        <livewire:pages.admin.subscription.subscription-note :subscription="$subscription"
                            :wire:key="'note-'.$subscription->id.$subscription->updated_at" />
                    </x-modal>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $items->links() }}
    </div>

</div>
