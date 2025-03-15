<div
    class="overflow-hidden relative mb-8 bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
    <div class="absolute inset-0 bg-gradient-to-br to-transparent from-primary-500/10"></div>
    <div class="relative">
        <div class="flex flex-col justify-between items-center md:space-x-4 md:flex-row">

            <div class="flex gap-2 items-center">
                <div class="p-4 bg-primary-100 dark:bg-primary-500/10">
                    <div class="flex gap-2 items-center rounded-md">
                        <img src="{{ Auth::user()->image_url }}" class="object-cover rounded-md size-14 md:size-28" alt="avatar">
                    </div>
                </div>

                <div>
                    <h1 class="text-sm font-bold md:text-2xl text-neutral-900 dark:text-neutral-100">
                        Welcome back, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}!
                    </h1>
                    <p class="mt-1 text-xs md:text-sm text-neutral-600 dark:text-neutral-400">
                        {{ now()->format('l, j F Y') }}
                    </p>
                </div>
            </div>


            <!-- Subscription Info -->
            @if($subscription)
            <div class="mx-3 mb-3 md:mx-0 md:mb-0">
                <div
                    class="overflow-hidden relative w-full max-w-md bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/10"></div>
                    <div class="relative p-3 lg:p-6">
                        <div class="flex flex-col justify-between items-center space-y-3 md:space-x-3 md:flex-row">
                            <!-- Left side: Plan name and icon -->
                            <div class="flex flex-col items-center md:space-y-3">
                                <i class="text-xl text-blue-500 md:text-3xl dark:text-blue-400 fas fa-crown"></i>
                                <h3 class="font-bold text-center text-blue-600 md:text-xl dark:text-blue-400">
                                    {{$subscription['plan_name']}}</h3>
                                <div class="text-sm text-center text-neutral-600 dark:text-neutral-400">
                                    <span>${{number_format($subscription['price'], 2)}}</span> /
                                    <span class="capitalize">{{$subscription['periodicity_type']}}</span>
                                </div>
                                <a wire:navigate href="{{ route('our.plans') }}"
                                    class="px-2 py-1 text-xs font-medium text-blue-500 bg-indigo-100 rounded-full dark:text-blue-400 dark:bg-blue-500/10">Upgrade / Downgrade</a>
                            </div>

                            <!-- Right side: Subscription details -->
                            <div class="flex-1 space-y-3">
                                <div class="space-y-2 space-y-">
                                    <div class="flex gap-5 justify-between items-center">
                                        <span
                                            class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Started:</span>
                                        <span class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{
                                            $subscription['started_at'] }}</span>
                                    </div>
                                    <div class="flex gap-5 justify-between items-center">
                                        <span
                                            class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Expires:</span>
                                        <span class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{
                                            $subscription['expired_at'] }}</span>
                                    </div>
                                    @if($subscription['plan_id'] != 1)
                                    <div class="flex gap-5 justify-between items-center">
                                        <span
                                            class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Remaining:</span>
                                        <span class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{
                                            $subscription['remaining_time'] }}</span>
                                    </div>
                                    @endif
                                    @if($subscription['plan_id'] != 1)
                                    <div class="flex gap-2 justify-center mt-3">
                                        <livewire:pages.user.subscription.renew.renew>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif


        </div>
    </div>
</div>
