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

    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Transaction ID</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Gateway</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($items as $payment)
                @php
                $user = $payment->user;
                @endphp
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <div class="flex gap-2 items-center w-max">
                            <img class="object-cover rounded-full size-10" src="{{ $user->image_url ?? 'default-avatar.png' }}"
                                alt="{{ $user->first_name }}" />
                            <div class="flex flex-col">
                                <span class="font-medium">
                                    {{ $user->first_name }} {{ $user->last_name}} - ( {{ $user->username }} )
                                    @if($user->deleted_at)
                                    <span class="text-xs text-red-500">(Soft Delete)</span>
                                    @endif
                                </span>
                                <span class="text-sm text-neutral-500">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 font-mono text-sm">{{ $payment->transaction_id }}</td>
                    <td class="p-4">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                    <td class="p-4">
                        <span
                            class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-100">
                            {{ ucfirst($payment->gateway) }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @switch($payment->status)
                                @case('approved') text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-100 @break
                                @case('pending') text-yellow-800 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-100 @break
                                @case('processing') text-blue-800 bg-blue-100 dark:bg-blue-900 dark:text-blue-100 @break
                                @case('failed') text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-100 @break
                                @case('cancelled') text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-100 @break
                                @case('refunded') text-purple-800 bg-purple-100 dark:bg-purple-900 dark:text-purple-100 @break
                            @endswitch">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="p-4 whitespace-nowrap">
                        {{ $payment->created_at->format('d/m/Y H:i A') }}
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <x-primary-info-button href="{{ route('admin.transactions.edit', $payment) }}" wire:navigate>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-primary-info-button>
                            <x-primary-info-button href="{{ route('admin.users.transactions', $user) }}" wire:navigate>
                                View
                            </x-primary-info-button>
                            @if(!$user->deleted_at)
                            <x-primary-info-button
                                onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $user->id }})">
                                Login
                            </x-primary-info-button>
                            @endif
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
</div>
