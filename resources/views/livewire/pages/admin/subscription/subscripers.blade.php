<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Tabs -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab buttons -->
        <div class="flex gap-2 mb-5 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button x-on:click="selectedTab = 'all'"
                :class="selectedTab === 'all' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">All</button>
            <button x-on:click="selectedTab = 'canceled'"
                :class="selectedTab === 'canceled' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Canceled</button>
            <button x-on:click="selectedTab = 'suppressed'"
                :class="selectedTab === 'suppressed' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Suppressed</button>
            <button x-on:click="selectedTab = 'expired'"
                :class="selectedTab === 'expired' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Expired</button>
        </div>

        <!-- All Tab Content -->
        <div x-show="selectedTab === 'all'">
            @include('livewire.pages.admin.subscription.partials.table', [
            'items' => $this->allSubscriptions,
            'search' => 'searchAll',
            'searchPlaceholder' => 'Search all subscriptions...'
            ])
        </div>

        <!-- Canceled Tab Content -->
        <div x-show="selectedTab === 'canceled'">
            @include('livewire.pages.admin.subscription.partials.table', [
            'items' => $this->canceledSubscriptions,
            'search' => 'searchCanceled',
            'searchPlaceholder' => 'Search canceled subscriptions...'
            ])
        </div>

        <!-- Suppressed Tab Content -->
        <div x-show="selectedTab === 'suppressed'">
            @include('livewire.pages.admin.subscription.partials.table', [
            'items' => $this->suppressedSubscriptions,
            'search' => 'searchSuppressed',
            'searchPlaceholder' => 'Search suppressed subscriptions...'
            ])
        </div>

        <!-- Expired Tab Content -->
        <div x-show="selectedTab === 'expired'">
            @include('livewire.pages.admin.subscription.partials.table', [
            'items' => $this->expiredSubscriptions,
            'search' => 'searchExpired',
            'searchPlaceholder' => 'Search expired subscriptions...'
            ])
        </div>
    </div>
</div>
