<div>

    <div x-data="{ refreshing: false }" @refresh-statics.window="$wire.refresh()">
        <!-- Header with Refresh Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Dashboard Statistics</h2>
            <button @click="refreshing = true; $wire.refresh().then(() => { refreshing = false; $dispatch('refresh-statics', {}) })"
                :class="{ 'opacity-50 cursor-not-allowed': refreshing }"
                class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 ease-in-out dark:text-white bg-primary-600 hover:bg-primary-700">
                <i class="mr-2 fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh'"></span>
            </button>
        </div>


    <div class="grid grid-cols-1 gap-3 md:grid-cols-4 lg:grid-cols-5">
        <!-- Total Users -->
        <div class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-blue-500 dark:text-blue-400 fas fa-users-rectangle"></i>
                    <span class="px-2 py-1 text-xs font-medium text-blue-500 bg-blue-50 rounded-full dark:text-blue-400 dark:bg-blue-500/10">Number of Users</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Users</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{$totalUsers}}</p>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-green-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-green-500 dark:text-green-400 fas fa-check-circle"></i>
                    <span class="px-2 py-1 text-xs font-medium text-green-500 bg-green-50 rounded-full dark:text-green-400 dark:bg-green-500/10">Active Subscriptions</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Active Subscriptions</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{$activeSubscriptions}}</p>
                </div>
            </div>
        </div>

        <!-- Server Count -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-purple-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-purple-500 dark:text-purple-400 fas fa-server"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-purple-500 bg-purple-50 rounded-full dark:text-purple-400 dark:bg-purple-500/10">Number of Server</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Servers</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{$serverCount}}</p>
                </div>
            </div>
        </div>

        <!-- Total Campaigns -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-yellow-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-yellow-500 dark:text-yellow-400 fas fa-envelope-circle-check"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-yellow-500 bg-yellow-50 rounded-full dark:text-yellow-400 dark:bg-yellow-500/10">Number of Campaign</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Campaigns</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{$totalCampaigns}}</p>
                </div>
            </div>
        </div>

        <!-- Stored Messages Count -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-teal-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-teal-500 dark:text-teal-400 fas fa-inbox"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-teal-500 bg-teal-50 rounded-full dark:text-teal-400 dark:bg-teal-500/10"><Nav></Nav> Messages</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Stored Messages</p>
                    <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">{{$storedMessages}}</p>
                </div>
            </div>
        </div>

<!-- Email Lists Count -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-indigo-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-indigo-500 dark:text-indigo-400 fas fa-list-check"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-indigo-500 bg-indigo-50 rounded-full dark:text-indigo-400 dark:bg-indigo-500/10">Number ofEmail Lists</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Email Lists</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{$totalEmailLists}}</p>
                </div>
            </div>
        </div>

        <!-- Total Emails Count -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-cyan-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-cyan-500 dark:text-cyan-400 fas fa-envelope"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-cyan-500 bg-cyan-50 rounded-full dark:text-cyan-400 dark:bg-cyan-500/10">Number of Emails</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Emails</p>
                    <p class="text-3xl font-bold text-cyan-600 dark:text-cyan-400">{{$totalEmails}}</p>
                </div>
            </div>
        </div>

        <!-- Payment Count -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-pink-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-pink-500 dark:text-pink-400 fas fa-credit-card"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-pink-500 bg-pink-50 rounded-full dark:text-pink-400 dark:bg-pink-500/10">Number of Payments</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Payments</p>
                    <p class="text-3xl font-bold text-pink-600 dark:text-pink-400">{{$paymentCount}}</p>
                </div>
            </div>
        </div>

        <!-- Total Payments Amount -->
        <div class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-red-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-red-500 dark:text-red-400 fas fa-money-bill-trend-up"></i>
                    <span class="px-2 py-1 text-xs font-medium text-red-500 bg-red-50 rounded-full dark:text-red-400 dark:bg-red-500/10">Total Payments</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Payments</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">${{number_format($totalPayments, 2)}}</p>
                </div>
            </div>
        </div>




    </div>
</div>
