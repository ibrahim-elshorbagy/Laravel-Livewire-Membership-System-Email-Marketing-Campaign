<div>
    <div
        class="relative mb-8 overflow-hidden transition-all duration-300 bg-white border rounded-lg shadow-sm dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
        <div class="absolute inset-0 bg-gradient-to-br to-transparent from-primary-500/10"></div>
        <div class="relative">
            <div class="flex flex-col items-center justify-between md:space-x-4 md:flex-row">

                <div class="flex items-center gap-2 my-2 md:my-0">
                    <div class="p-4 bg-primary-100 dark:bg-primary-500/10">
                        <div class="flex items-center gap-2 rounded-md">
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
                            {{ now()->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d-m-Y h:i:s A') }}

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
                                            <p
                                                class="mt-2 text-xs font-medium text-green-600 md:text-sm dark:text-green-400">
                                                A new verification link has been sent to your email address.
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <span
                                        class="inline-flex mt-2 overflow-hidden text-xs font-medium text-blue-500 bg-white rounded-lg w-fit dark:bg-blue-950 dark:text-blue-500">
                                        <span class="flex items-center gap-1 px-2 py-1 bg-blue-500/10">
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
                @if ($subscription)
                    <div class=" md:mx-0 md:mb-0">
                        <div
                            class="relative w-full max-w-full overflow-hidden transition-all duration-300 bg-white border rounded-lg shadow-sm lg:max-w-3xl xl:max-w-4xl dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
                            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/10"></div>
                            <div class="relative p-3 lg:p-6">
                                <div
                                    class="flex flex-col items-center justify-between gap-6 md:gap-8 lg:gap-12 md:flex-row">
                                    <!-- Left side: Plan name and icon -->
                                    <div class="flex flex-col items-center gap-3 md:gap-4">
                                        <i
                                            class="text-xl text-blue-500 md:text-3xl dark:text-blue-400 fas fa-crown"></i>
                                        <h3 class="font-bold text-center text-blue-600 md:text-xl dark:text-blue-400">
                                            {{ $subscription['plan_name'] }}</h3>
                                        <div class="text-sm text-center text-neutral-600 dark:text-neutral-400">
                                            <span>${{ number_format($subscription['price'], 2) }}</span> /
                                            <span class="capitalize">{{ $subscription['periodicity_type'] }}</span>
                                        </div>
                                        <a href="{{ route('our.plans') }}"
                                            class="px-2 py-1 text-xs font-medium text-blue-500 bg-indigo-100 rounded-full dark:text-blue-400 dark:bg-blue-500/10">Upgrade
                                            / Downgrade</a>
                                    </div>

                                    <!-- Right side: Subscription details -->
                                    <div class="flex-1 w-full space-y-4 md:w-auto">
                                        <div class="space-y-2 space-y-">
                                            <div class="flex items-center justify-between gap-5">
                                                <span
                                                    class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Started:</span>
                                                <span
                                                    class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{ $subscription['started_at'] }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-5">
                                                <span
                                                    class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Expires:</span>
                                                <span
                                                    class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{ $subscription['expired_at'] }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-5">
                                                <span
                                                    class="text-xs font-medium md:text-sm text-neutral-600 dark:text-neutral-400">Remaining:</span>
                                                <span
                                                    class="text-xs text-blue-600 md:text-sm dark:text-blue-400">{{ $subscription['remaining_time'] }}</span>
                                            </div>
                                            @if ($subscription['plan_id'] != 1)
                                                <div class="flex justify-center gap-2 mt-3">
                                                    <livewire:pages.user.subscription.renew.renew>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @role('user')
                        <div
                            class="p-4 text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-red-900/10 dark:border-red-300/10 dark:text-red-300">
                            <div class="flex items-center gap-2">
                                <i class="text-red-600 fas fa-exclamation-triangle size-4 dark:text-red-300"></i>
                                <span class="text-sm font-bold uppercase md:text-lg">Action Required: No Active
                                    Subscription!</span>
                            </div>

                            <p class="mt-2 mb-3 text-xs font-medium md:text-sm">
                                You are currently not subscribed to any plan.<br> Please subscribe to a suitable plan to
                                unlock all features.
                            </p>

                            <x-primary-danger-link href="{{ route('our.plans') }}" wire:navigate>
                                <div class=>
                                    <i class="fas fa-crown size-4 me-2"></i>
                                    <span>Choose a suitable plan to subscribe</span>
                                </div>
                            </x-primary-danger-link>
                        </div>
                    @endrole
                @endif

            </div>
        </div>




    </div>

    @if ($showWarning)
        <div
            class="p-4 my-4 text-yellow-800 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/10 dark:border-yellow-300/10 dark:text-yellow-300">
            <div class="flex items-center gap-2">
                <svg class="size-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
                        clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Pending Transaction Alert!</span>
            </div>
            @role('admin')
                <p class="mt-2 text-sm">There are pending transactions awaiting your approval. Please check the transactions
                    page to review and process these payments.</p>
            @endrole
            @role('user')
                <p class="mt-2 text-sm">There is a pending transaction awaiting admin approval. Please ensure you attach a
                    photo/Pdf of
                    the payment/transfer notification and the transfer number to the transactions page.</p>
            @endrole
        </div>
    @endif


</div>
