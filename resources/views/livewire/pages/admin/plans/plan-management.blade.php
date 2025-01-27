<div
    class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Tabs -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab buttons -->
        <div class="flex gap-2 mb-5 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button x-on:click="selectedTab = 'monthly'"
                :class="selectedTab === 'monthly' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-300'"
                class="px-4 py-2 text-sm h-min" role="tab">Monthly Plans</button>
            <button x-on:click="selectedTab = 'yearly'"
                :class="selectedTab === 'yearly' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-300'"
                class="px-4 py-2 text-sm h-min" role="tab">Yearly Plans</button>
        </div>

        <!-- Monthly Plans Tab Content -->
        <div x-show="selectedTab === 'monthly'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.plans.partials.plans-table', [
                'plans' => $monthlyPlans,
                'periodType' => 'Monthly'
                ])
            </div>
        </div>

        <!-- Yearly Plans Tab Content -->
        <div x-show="selectedTab === 'yearly'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.plans.partials.plans-table', [
                'plans' => $yearlyPlans,
                'periodType' => 'Yearly'
                ])
            </div>
        </div>
    </div>
</div>
