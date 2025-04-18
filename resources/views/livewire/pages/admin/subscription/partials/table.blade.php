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
                @if($search)
                <div class="flex absolute inset-y-0 right-0 items-center pr-3">
                    <button wire:click="$set('{{ $search }}', '')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-xs text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-xs bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th class="p-4">Subscriber</th>
                    <th class="p-4">Plan</th>
                    <th class="p-4 text-center">Limits</th>
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
                        <span
                            class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full text-nowrap">
                            {{ $subscription->plan->name }}
                        </span>
                    </td>
                    <td class="p-4 text-nowrap">
                        <div class="flex flex-col gap-2 text-center">
                            @if($subscription->suppressed_at)
                            <span class="text-xs text-yellow-600 dark:text-yellow-400">Suppressed</span>
                            @else
                            @php
                            $features = $subscription->getFeatureData();
                            $subscribers = $features['Subscribers Limit'] ?? ['used' => 0, 'limit' => 0];
                            $emails = $features['Email Sending'] ?? ['used' => 0, 'limit' => 0];
                            @endphp
                            <div class="flex flex-col items-center">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Subscribers:</span>
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-200">
                                    {{ $subscribers['used'] }}/{{ $subscribers['limit'] }}
                                </span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Emails:</span>
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-200">
                                    {{ $emails['used'] }}/{{ $emails['limit'] }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="p-4">{{ $subscription->started_at->format('d/m/Y h:i:s A') }}</td>
                    <td class="p-4">{{ $subscription->expired_at?->format('d/m/Y h:i:s A') }}</td>
                    <td class="p-4">
                        @if($subscription->payments->isNotEmpty())
                        @php
                        $payment = $subscription->payments->first();
                        @endphp
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
                        <span
                            class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full text-nowrap">
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
                            <x-primary-info-button href="{{ route('admin.users.transactions', $subscriber) }}"
                                wire:navigate>
                                Transactions
                            </x-primary-info-button>
                            @if(!$subscriber->deleted_at)
                            <x-primary-info-button
                                onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $subscriber->id }})">
                                Login
                            </x-primary-info-button>
                            @endif
                            <x-primary-info-button
                                x-on:click="$dispatch('open-modal', 'subscription-note-modal'); $wire.selectedSubscriptionId = {{ $subscription->id }}; $wire.noteContent = '{{ $subscription->note?->content }}'"
                                class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                <i class="fa-solid fa-note-sticky"></i>
                            </x-primary-info-button>


                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $items->links() }}
    </div>

    <!-- Note Modal -->
    <x-modal name="subscription-note-modal" :maxWidth="'2xl'">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Subscription Note</h2>
            <form wire:submit.prevent="updateNote">
                <div class="mt-6">
                    <x-primary-textarea wire:model="noteContent" placeholder="Enter note for this subscription..."
                        class="w-full h-64">
                    </x-primary-textarea>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        Save
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </x-modal>


</div>
