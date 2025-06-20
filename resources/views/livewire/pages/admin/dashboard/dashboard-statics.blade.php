<div>

    <div x-data="{ refreshing: false }" @refresh-statics.window="$wire.refresh()">
        <!-- Header with Refresh Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-bold md:text-2xl text-neutral-900 dark:text-neutral-100">Dashboard Statistics</h2>
            <button
                @click="refreshing = true; $wire.refresh().then(() => { refreshing = false; $dispatch('refresh-statics', {}) })"
                :class="{ 'opacity-50 cursor-not-allowed': refreshing }"
                class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 ease-in-out dark:text-white bg-primary-600 hover:bg-primary-700">
                <i class="mr-2 fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh'"></span>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            <!--  Users & Subscriptions -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-blue-500 md:text-3xl dark:text-blue-400 fas fa-users-rectangle"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-blue-500 bg-blue-50 rounded-full md:text-sm dark:text-blue-400 dark:bg-blue-500/10">User
                            Management</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <a href="{{ route('admin.users') }}" wire:navigate
                            class="flex-1 cursor-pointer hover:opacity-75 transition-opacity">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Users</p>
                            <p class="font-bold text-blue-600 md:text-2xl dark:text-blue-400">{{$totalUsers}}</p>
                        </a>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <a href="{{ route('admin.subscriptions') }}" wire:navigate
                            class="flex-1 cursor-pointer hover:opacity-75 transition-opacity">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Subscriptions</p>
                            <p class="font-bold text-blue-600 md:text-2xl dark:text-blue-400">{{$activeSubscriptions}}
                            </p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Server Count -->
            <a href="{{ route('admin.servers') }}" wire:navigate
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-purple-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-purple-500 md:text-3xl dark:text-purple-400 fas fa-server"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-purple-500 bg-purple-50 rounded-full md:text-sm dark:text-purple-400 dark:bg-purple-500/10">Sending bots
                            Management</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Sending bots</p>
                        <p class="font-bold text-purple-600 md:text-2xl dark:text-purple-400">{{$serverCount}}</p>
                    </div>
                </div>
            </a>

            <!-- Combined Campaign Stats -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-yellow-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-yellow-500 md:text-3xl dark:text-yellow-400 fas fa-envelope-circle-check"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-yellow-500 bg-yellow-50 rounded-full mdtext-sm dark:text-yellow-400 dark:bg-yellow-500/10">Campaign
                            Management</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total</p>
                            <p class="font-bold text-yellow-600 md:text-2xl dark:text-yellow-400">{{$totalCampaigns}}</p>
                        </div>
                        <div class="mx-2 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Sending</p>
                            <p class="font-bold text-yellow-600 md:text-2xl dark:text-yellow-400">{{$sendingCampaigns}}</p>
                        </div>
                        <div class="mx-2 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Messages</p>
                            <p class="font-bold text-yellow-600 md:text-2xl dark:text-yellow-400">{{$storedMessages}}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Email Stats -->
            <div
                class="overflow-hidden relative rounded-lg border shadow-sm transition-all duration-300 bg-white/90 dark:bg-neutral-800/90 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                <div class="absolute inset-0 bg-gradient-to-br to-transparent from-indigo-500/20"></div>
                <div class="relative p-3 lg:p-6">
                    <div class="flex justify-between items-center">
                        <i class="text-indigo-500 md:text-3xl dark:text-indigo-400 fas fa-envelope"></i>
                        <span
                            class="px-3 py-1.5 text-xs font-medium text-indigo-500 bg-indigo-50 rounded-full md:text-sm dark:text-indigo-400 dark:bg-indigo-500/10">Email
                            Management</span>
                    </div>
                    <div class="flex items-center mt-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Lists</p>
                            <p class="font-bold text-indigo-600 md:text-2xl dark:text-indigo-400">{{$totalEmailLists}}
                            </p>
                        </div>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400"> Emails</p>
                            <p class="font-bold text-indigo-600 md:text-2xl dark:text-indigo-400">{{$totalEmails}}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Payment Stats -->
            <a href="{{ route('admin.payment.transactions') }}" wire:navigate
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
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400"> Payments</p>
                            <p class="font-bold text-green-600 md:text-2xl dark:text-green-400">{{$paymentCount}}</p>
                        </div>
                        <div class="mx-4 w-px h-16 bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400"> Revenue</p>
                            <p class="font-bold text-green-600 md:text-2xl dark:text-green-400">
                                ${{number_format($totalPayments, 2)}}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
