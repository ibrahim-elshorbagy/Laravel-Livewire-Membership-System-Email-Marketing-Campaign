<div>
    <div x-data="{ refreshing: false }" @refresh-statics.window="$wire.refresh()">
        <!-- Header with Refresh Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-bold md:md:text-2xl text-neutral-900 dark:text-neutral-100">Dashboard Statistics</h2>
            <button
                @click="refreshing = true; $wire.refresh().then(() => { refreshing = false; $dispatch('refresh-statics', {}) })"
                :class="{ 'opacity-50 cursor-not-allowed': refreshing }"
                class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 ease-in-out dark:text-white bg-primary-600 hover:bg-primary-700">
                <i class="mr-2 fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh'"></span>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 mt-8 md:grid-cols-2 lg:grid-cols-4">
            <!-- Server Count -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-purple-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-purple-500 md:text-3xl dark:text-purple-400 fas fa-server"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-purple-500 bg-purple-50 rounded-full md:text-sm dark:text-purple-400 dark:bg-purple-500/10">My
                            Servers</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400">Servers</p>
                            <p class="font-bold text-purple-600 md:text-2xl dark:text-purple-400">{{ $serverCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Email Stats -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-teal-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-teal-500 md:text-3xl dark:text-teal-400 fas fa-envelope-open-text"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-teal-500 bg-teal-50 rounded-full md:text-sm dark:text-teal-400 dark:bg-teal-500/10">Email
                            Management</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400">Lists</p>
                            <p class="font-bold text-teal-600 md:text-2xl dark:text-teal-400">{{ $totalEmailLists }}</p>
                        </div>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400"> Emails</p>
                            <p class="font-bold text-teal-600 md:text-2xl dark:text-teal-400">{{ $totalEmails }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Campaign Stats -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-indigo-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-indigo-500 md:text-3xl dark:text-indigo-400 fas fa-paper-plane"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-indigo-500 bg-indigo-50 rounded-full md:text-sm dark:text-indigo-400 dark:bg-indigo-500/10">Campaign
                            Management</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400">Campaigns</p>
                            <p class="font-bold text-indigo-600 md:text-2xl dark:text-indigo-400">{{ $totalCampaigns }}</p>
                        </div>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400">Messages</p>
                            <p class="font-bold text-indigo-600 md:text-2xl dark:text-indigo-400">{{ $storedMessages }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Payment Stats -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-green-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-green-500 md:text-3xl dark:text-green-400 fas fa-money-bill-wave"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-green-500 bg-green-50 rounded-full md:text-sm dark:text-green-400 dark:bg-green-500/10">Payment
                            Statistics</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400">Payments</p>
                            <p class="font-bold text-green-600 md:text-2xl dark:text-green-400">{{ $paymentCount }}</p>
                        </div>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium md:text-xl text-neutral-600 dark:text-neutral-400"> Amount</p>
                            <p class="font-bold text-green-600 md:text-2xl dark:text-green-400">${{
                                number_format($totalPayments, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
