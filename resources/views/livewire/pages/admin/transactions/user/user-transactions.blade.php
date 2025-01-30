<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Header -->
    <div class="flex flex-col items-center justify-between mb-6 md:flex-row">

        <div class="flex flex-col items-center gap-2 mt-4 w-max md:mt-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Transactions for
            </h2>
            <div class="flex gap-2 mt-4 w-max md:mt-0">
                <img class="object-cover rounded-full size-10" src="{{ $user->image_url ?? 'default-avatar.png' }}"
                    alt="{{ $user->name }}" />
                <div class="flex flex-col">
                    <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }} - ( {{ $user->username }} )</span>
                    <span class="text-sm text-neutral-500">{{ $user->email }}</span>
                </div>
            </div>
        </div>
        <x-primary-info-button href="{{ route('admin.users') }}" wire:navigate>
            Back to Users
        </x-primary-info-button>
    </div>

    <!-- Tabs -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab buttons -->
        <div class="flex gap-2 mb-5 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button x-on:click="selectedTab = 'all'"
                :class="selectedTab === 'all' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">All</button>
            <button x-on:click="selectedTab = 'pending'"
                :class="selectedTab === 'pending' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Pending</button>
            <button x-on:click="selectedTab = 'approved'"
                :class="selectedTab === 'approved' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Approved</button>
            <button x-on:click="selectedTab = 'failed'"
                :class="selectedTab === 'failed' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Failed</button>
            <button x-on:click="selectedTab = 'cancelled'"
                :class="selectedTab === 'cancelled' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Cancelled</button>
            <button x-on:click="selectedTab = 'refunded'"
                :class="selectedTab === 'refunded' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Refunded</button>
        </div>

        <!-- All Tab Content -->
        <div x-show="selectedTab === 'all'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->allPayments,
            'search' => 'searchAll',
            'searchPlaceholder' => 'Search all payments...'
            ])
        </div>

        <!-- Pending Tab Content -->
        <div x-show="selectedTab === 'pending'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->pendingPayments,
            'search' => 'searchPending',
            'searchPlaceholder' => 'Search pending payments...'
            ])
        </div>

        <!-- Approved Tab Content -->
        <div x-show="selectedTab === 'approved'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->approvedPayments,
            'search' => 'searchApproved',
            'searchPlaceholder' => 'Search approved payments...'
            ])
        </div>

        <!-- Failed Tab Content -->
        <div x-show="selectedTab === 'failed'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->failedPayments,
            'search' => 'searchFailed',
            'searchPlaceholder' => 'Search failed payments...'
            ])
        </div>

        <!-- Cancelled Tab Content -->
        <div x-show="selectedTab === 'cancelled'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->cancelledPayments,
            'search' => 'searchCancelled',
            'searchPlaceholder' => 'Search cancelled payments...'
            ])
        </div>

        <!-- Refunded Tab Content -->
        <div x-show="selectedTab === 'refunded'">
            @include('livewire.pages.admin.transactions.user.partials.table', [
            'items' => $this->refundedPayments,
            'search' => 'searchRefunded',
            'searchPlaceholder' => 'Search refunded payments...'
            ])
        </div>
    </div>
</div>
