<div class="p-6 space-y-6 bg-white rounded-lg shadow dark:bg-neutral-900">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
            Transaction History
        </h2>
    </div>

    <!-- Tabs -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab buttons -->
        <div class="flex gap-2 mb-5 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button wire:click="$set('selectedTab', 'all')"
                :class="{'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500': selectedTab === 'all', 'text-neutral-600 dark:text-neutral-400': selectedTab !== 'all'}"
                class="px-4 py-2 text-sm h-min" role="tab">All Transactions</button>

            <button wire:click="$set('selectedTab', 'approved')"
                :class="{'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500': selectedTab === 'approved', 'text-neutral-600 dark:text-neutral-400': selectedTab !== 'approved'}"
                class="px-4 py-2 text-sm h-min" role="tab">approved</button>

            <button wire:click="$set('selectedTab', 'pending')"
                :class="{'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500': selectedTab === 'pending', 'text-neutral-600 dark:text-neutral-400': selectedTab !== 'pending'}"
                class="px-4 py-2 text-sm h-min" role="tab">Pending</button>

            <button wire:click="$set('selectedTab', 'failed')"
                :class="{'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500': selectedTab === 'failed', 'text-neutral-600 dark:text-neutral-400': selectedTab !== 'failed'}"
                class="px-4 py-2 text-sm h-min" role="tab">Failed/Cancelled</button>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
                <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                    <tr>
                        <th scope="col" class="p-4">Date</th>
                        <th scope="col" class="p-4">Plan</th>
                        <th scope="col" class="p-4">Amount</th>
                        <th scope="col" class="p-4">Status</th>
                        <th scope="col" class="p-4">Gateway</th>
                        {{-- <th scope="col" class="p-4">Transaction ID</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        <td class="p-4">
                            {{ $payment->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $payment->plan->name }}
                            </div>
                        </td>
                        <td class="p-4">
                            ${{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                        </td>
                        <td class="p-4">
                            <span @class([ 'px-2 py-1 text-xs font-medium rounded-full'
                                , 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'=> $payment->status
                                === 'approved',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' =>
                                $payment->status === 'pending',
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' =>
                                in_array($payment->status, ['failed', 'cancelled']),
                                ])>
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>

                        <td class="p-4">
                            <div class="flex items-center">
                                @if($payment->gateway === 'paypal')
                                <i class="mr-2 fab fa-paypal"></i>
                                @endif
                                {{ ucfirst($payment->gateway) }}
                            </div>
                        </td>
                        {{-- <td class="p-4">
                            <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $payment->transaction_id ? $payment->transaction_id : 'N/A' }}
                            </div>
                        </td> --}}
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-neutral-600 dark:text-neutral-400">
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>
