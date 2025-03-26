<div
    class="overflow-hidden relative mb-8 bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
    <div class="absolute inset-0 bg-gradient-to-br to-transparent from-primary-500/10"></div>
    <div class="relative">
        <div class="flex flex-col justify-between items-center md:space-x-4 md:flex-row">

            <div class="flex gap-2 items-center my-2 md:my-0">
                <div class="p-4 bg-primary-100 dark:bg-primary-500/10">
                    <div class="flex gap-2 items-center rounded-md">
                        <img src="{{ Auth::user()->image_url }}" class="object-cover rounded-md size-14 md:size-28"
                            alt="avatar">
                    </div>
                </div>

                <div>
                    <h1 class="text-sm font-bold md:flex-row md:text-2xl text-neutral-900 dark:text-neutral-100">
                        <p>Welcome back,</p>
                        <p> {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}!</p>
                    </h1>
                    <p class="mt-1 text-xs md:text-sm text-neutral-600 dark:text-neutral-400">
                        {{ now()->format('l, j F Y') }}
                    </p>

                    @role('user')
                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail)
                    @if (!auth()->user()->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            <span class="text-red-500">Unverified.</span>
                            <button wire:click.prevent="sendVerification"
                                class="text-xs text-left text-gray-600 underline rounded-md md:text-sm dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                Click here to resend the verification email.
                            </button>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs font-medium text-green-600 md:text-sm dark:text-green-400">
                            A new verification link has been sent to your email address.
                        </p>
                        @endif
                    </div>
                    @else
                    <span
                        class="inline-flex overflow-hidden mt-2 text-xs font-medium text-blue-500 bg-white rounded-lg w-fit dark:bg-blue-950 dark:text-blue-500">
                        <span class="flex gap-1 items-center px-2 py-1 bg-blue-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"
                                fill="currentColor" class="size-4">
                                <path fill-rule="evenodd"
                                    d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                    clip-rule="evenodd" />
                            </svg>
                            Verified.
                        </span>
                    </span>
                    @endif
                    @endif
                    @endrole

                </div>
            </div>

            <!-- Subscription Info -->
            @if($subscription)
            <div class="mx-3 mb-3 md:mx-0 md:mb-0">
                <div
                    class="overflow-hidden relative w-full max-w-full bg-white rounded-lg border shadow-sm transition-all duration-300 lg:max-w-3xl xl:max-w-4xl dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/10"></div>
                    <div class="relative p-3 lg:p-6">
                        <div class="flex flex-col gap-6 justify-between items-center md:gap-8 lg:gap-12 md:flex-row">
                            <!-- Left side: Plan name and icon -->
                            <div class="flex flex-col gap-3 items-center md:gap-4">
                                <i class="text-xl text-blue-500 md:text-3xl dark:text-blue-400 fas fa-crown"></i>
                                <h3 class="font-bold text-center text-blue-600 md:text-xl dark:text-blue-400">
                                    {{$subscription['plan_name']}}</h3>
                                <div class="text-sm text-center text-neutral-600 dark:text-neutral-400">
                                    <span>${{number_format($subscription['price'], 2)}}</span> /
                                    <span class="capitalize">{{$subscription['periodicity_type']}}</span>
                                </div>
                                <a href="{{ route('our.plans') }}"
                                    class="px-2 py-1 text-xs font-medium text-blue-500 bg-indigo-100 rounded-full dark:text-blue-400 dark:bg-blue-500/10">Upgrade
                                    / Downgrade</a>
                            </div>

                            <!-- Right side: Subscription details -->
                            <div class="flex-1 space-y-4 w-full md:w-auto">
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
                                    <div class="flex gap-5 justify-between items-center">
                                        <span
                                            class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Remaining:</span>
                                        <span class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{
                                            $subscription['remaining_time'] }}</span>
                                    </div>
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