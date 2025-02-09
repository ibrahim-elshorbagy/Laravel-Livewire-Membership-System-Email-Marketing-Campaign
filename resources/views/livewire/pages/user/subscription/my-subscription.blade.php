<div class="max-w-2xl py-6 mx-auto sm:px-6 lg:px-8">
    @if($subscription)
    <div class="overflow-hidden bg-neutral-50 dark:bg-neutral-900 rounded-xl">
        <!-- Header with Plan Name -->
        <div class="px-6 py-8 text-center bg-indigo-500 dark:bg-indigo-700">
            <h2 class="text-2xl font-bold text-white ">
                {{ $subscription->plan->name }}
            </h2>
            <p class="mt-2 text-neutral-200 ">
                Your Active Subscription
            </p>
        </div>

        <!-- Subscription Details -->
        <div class="p-6">
            <!-- Price -->
            @if($subscription->plan->id != 1)
            <div class="flex items-baseline justify-center">
                <span class="text-4xl font-bold text-neutral-900 dark:text-neutral-100">
                    ${{ number_format($subscription->plan->price, 2) }}
                </span>
                <span class="ml-2 text-neutral-600 dark:text-neutral-400">
                    /{{ strtolower($subscription->plan->periodicity_type) }}
                </span>
            </div>
            @endif
            <!-- Subscription Info -->
            <div class="mt-8 space-y-4">
                @if($subscription->plan->id != 1)
                <!-- Start Date -->
                <div class="flex items-center justify-between px-4 py-3 bg-white rounded-lg dark:bg-neutral-800">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt text-neutral-600 dark:text-neutral-400"></i>
                        <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">Start Date</span>
                    </div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ $subscription->started_at->format('M d, Y') }}
                    </span>
                </div>

                <!-- Expiry Date -->
                <div class="flex items-center justify-between px-4 py-3 bg-white rounded-lg dark:bg-neutral-800">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-neutral-600 dark:text-neutral-400"></i>
                        <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">Expiry Date</span>
                    </div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ $subscription->expired_at?->format('M d, Y') ?? 'N/A' }}
                    </span>
                </div>

                <!-- Remaining Time -->
                <div class="flex items-center justify-between px-4 py-3 bg-white rounded-lg dark:bg-neutral-800">
                    <div class="flex items-center">
                        <i class="fas fa-hourglass-half text-neutral-600 dark:text-neutral-400"></i>
                        <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">Remaining
                            Time</span>
                    </div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ $subscription->remaining_time }}
                    </span>
                </div>

                <!-- Status -->
                <div class="flex items-center justify-between px-4 py-3 bg-white rounded-lg dark:bg-neutral-800">
                    <div class="flex items-center">
                        <i class="fas fa-circle-notch text-neutral-600 dark:text-neutral-400"></i>
                        <span class="ml-3 text-sm font-medium text-neutral-900 dark:text-neutral-100">Status</span>
                    </div>
                    <span @class([ 'px-2 py-1 text-xs font-medium rounded-full'
                        , 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'=>
                        !$subscription->canceled_at,
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $subscription->canceled_at,
                        ])>
                        {{ $subscription->canceled_at ? 'Cancelled' : 'Active' }}
                    </span>
                </div>
                @endif
                <!-- Features -->
                <div class="px-4 py-3 bg-white rounded-lg dark:bg-neutral-800">
                    <h3 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Features Included</h3>
                    <ul class="mt-4 space-y-4">
                        @foreach($subscription->plan->features as $feature)
                        <li class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm text-neutral-600 dark:text-neutral-400">
                                    <svg class="flex-shrink-0 w-4 h-4 mr-2 text-green-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ $feature->name }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                        {{ (int)auth()->user()->balance($feature->name) }} / {{
                                        (int)$feature->pivot->charges }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-neutral-700">
                                @php
                                $percentage = ($feature->pivot->charges > 0)
                                ? (auth()->user()->balance($feature->name) / $feature->pivot->charges) * 100
                                : 0;
                                $colorClass = $percentage > 75
                                ? 'bg-green-500'
                                : ($percentage > 25
                                ? 'bg-yellow-500'
                                : 'bg-red-500');
                                @endphp
                                <div class="h-2 rounded-full transition-all {{ $colorClass }}"
                                    style="width: {{ $percentage }}%">
                                </div>
                            </div>

                            <!-- Usage Info -->
                            <div class="flex justify-between text-xs">
                                <span class="text-neutral-500 dark:text-neutral-400">
                                    @if($percentage <= 0) Limit reached @elseif($percentage <=25) Running low
                                        @elseif($percentage <=75) Good usage @else Plenty available @endif </span>
                                        <span
                                            class="font-medium {{ $colorClass === 'bg-green-500' ? 'text-green-600 dark:text-green-400' : ($colorClass === 'bg-yellow-500' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                            {{ number_format($percentage, 0) }}%
                                        </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Cancel Button -->
            @if($subscription->plan->id != 1)
            @if(is_null($subscription->canceled_at))
            <div class="mt-8">
                <button wire:click="cancelSubscription" wire:loading.attr="disabled"
                    wire:confirm="Are you sure you want to cancel your subscription?" type="button"
                    @class([ 'w-full px-4 py-2 text-sm font-medium text-white transition rounded-lg'
                    , 'bg-red-600 hover:bg-red-700'=> !$isProcessing,
                    'bg-red-400 cursor-not-allowed' => $isProcessing,
                    ])>
                    <span wire:loading.remove wire:target="cancelSubscription">
                        Cancel Subscription
                    </span>
                    <span wire:loading wire:target="cancelSubscription">
                        <i class="fas fa-spinner fa-spin"></i>
                        Processing...
                    </span>
                </button>
                <p class="mt-2 text-xs text-center text-neutral-600 dark:text-neutral-400">
                    You'll still have access until your current period ends
                </p>
            </div>
            @endif
            @endif
        </div>

    </div>

    <div class="flex items-center justify-center my-10">
        <button wire:navigate href="{{ route('our.plans') }}" class="relative px-6 py-3 text-lg font-semibold text-white transition-all duration-300 ease-in-out
            bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700
            rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5
            overflow-hidden group">
            <span class="relative z-10 flex items-center">
                Upgrade
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 ml-2 transition-transform duration-300 ease-in-out transform group-hover:translate-x-1"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </span>
            <div
                class="absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-out transform translate-x-full -skew-x-12 bg-white/10 group-hover:translate-x-0">
            </div>
        </button>
    </div>
    @else
    <!-- No Subscription State -->
    <div class="p-8 text-center bg-neutral-50 dark:bg-neutral-900 rounded-xl">
        <svg class="w-12 h-12 mx-auto text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01M12 12h.01" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-neutral-100">No Active Subscription</h3>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
            You don't have any active subscription at the moment.
        </p>
        <div class="mt-6">
            <div class="flex items-center justify-center my-10">
                <button wire:navigate href="{{ route('our.plans') }}" class="relative px-6 py-3 text-lg font-semibold text-white transition-all duration-300 ease-in-out
                            bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700
                            rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5
                            overflow-hidden group">
                    <span class="relative z-10 flex items-center">
                        Subscribe Now
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 ml-2 transition-transform duration-300 ease-in-out transform group-hover:translate-x-1"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                    <div
                        class="absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-out transform translate-x-full -skew-x-12 bg-white/10 group-hover:translate-x-0">
                    </div>
                </button>
            </div>
        </div>
    </div>


    @endif


</div>
