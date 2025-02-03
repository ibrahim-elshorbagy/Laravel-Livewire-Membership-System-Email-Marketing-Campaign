@props([
'items',
'search',
'searchPlaceholder' => 'Search...'
])

<div>
    <!-- Search Box -->
    <div class="flex items-center justify-between mb-4">
        <div class="w-full">
            <div class="relative">
                <x-text-input wire:model.live.debounce.600ms="{{ $search }}" id="{{ $search }}" type="text"
                    class="w-full py-2 pl-10 pr-20" placeholder="{{ $searchPlaceholder }}" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
                @if($$search)
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button wire:click="$set('{{ $search }}', '')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th class="p-4">Transaction ID</th>
                    <th class="p-4">Plan</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Gateway</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Subscription</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @forelse($items as $payment)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <td class="p-4 font-mono text-sm">
                        {{ $payment->transaction_id }}
                        @if($payment->gateway_subscription_id)
                        <div class="mt-1 text-xs text-neutral-500">
                            Sub ID: {{ $payment->gateway_subscription_id }}
                        </div>
                        @endif
                    </td>
                    <td class="p-4">
                        <span class="font-medium text-nowrap">{{ $payment->plan->name }}</span>
                        <div class="text-sm text-neutral-500">
                            ${{ number_format($payment->plan->price, 2) }} / {{ $payment->plan->frequency }}
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="font-medium">${{ number_format($payment->amount, 2) }}</span>
                        <div class="text-sm text-neutral-500">{{ strtoupper($payment->currency) }}</div>
                    </td>
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
                                @case('failed') text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-100 @break
                                @case('cancelled') text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-100 @break
                                @case('refunded') text-purple-800 bg-purple-100 dark:bg-purple-900 dark:text-purple-100 @break
                            @endswitch">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="p-4">
                        @if($payment->subscription)
                        <div class="text-sm">
                            <div class="text-neutral-500 text-nowrap">
                                {{ $payment->subscription->started_at?->format('M d, Y') }} to
                                {{ $payment->subscription->expired_at?->format('M d, Y') }}
                            </div>
                            @if($payment->subscription->expired_at && $payment->subscription->expired_at->isFuture())
                                @if($payment->subscription->plan->name != "Trial")
                                <div class="mt-1 text-xs text-green-600 dark:text-green-400 text-nowrap">
                                    @if ($payment->subscription)
                                    Expires in {{ \Carbon\Carbon::parse($payment->subscription->expired_at)->diffForHumans(now(), [
                                    'parts' => 3,
                                    'join' => true,
                                    'syntax' => \Carbon\Carbon::DIFF_RELATIVE_TO_NOW,
                                    ]) }}
                                    @endif
                                </div>
                                @endif
                            @endif
                        </div>
                        @else
                        <span class="text-neutral-500">-</span>
                        @endif
                    </td>
                    <td class="p-4 text-nowrap">
                        <div class="text-sm">
                            {{ $payment->created_at->format('M d, Y') }}
                            {{-- <div class="text-neutral-500">
                                {{ $payment->created_at->format('H:i') }}
                            </div> --}}
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <x-primary-info-button href="{{ route('admin.transactions.edit', $payment) }}"
                                wire:navigate>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-primary-info-button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-4 text-center text-neutral-500">
                        No transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
