<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

    <!-- Subscription Plans -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Note about downgrading -->
        @auth
        <div
            class="p-4 my-4 text-yellow-800 bg-yellow-50 rounded-lg border border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-300/10 dark:text-yellow-300">
            <div class="flex gap-2 items-center">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
                        clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Downgrade Alert!</span>
            </div>
            <p class="mt-2 text-sm">To downgrade your current package, we advise you to wait until the current package
                expires and then
                choose the new package when renewing your subscription.</p>
        </div>
        @endauth

        <!-- Tab Navigation -->
        <div class="flex overflow-x-auto gap-2 mb-6 border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button x-on:click="selectedTab = 'monthly'"
                x-bind:class="selectedTab === 'monthly' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600 dark:text-neutral-400'"
                class="px-4 py-2 text-sm h-min" role="tab">
                Monthly Plans
            </button>
            <button x-on:click="selectedTab = 'yearly'"
                x-bind:class="selectedTab === 'yearly' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600 dark:text-neutral-400'"
                class="px-4 py-2 text-sm h-min" role="tab">
                Yearly Plans
            </button>
        </div>

        <!-- Monthly Plans -->
        <div x-show="selectedTab === 'monthly'" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach($monthlyPlans as $plan)
            <article
                class="flex overflow-hidden flex-col p-3 w-full rounded-lg md:p-6 group bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                {{-- @if($loop->first)
                <span
                    class="px-2 py-1 ml-auto text-xs font-medium bg-black rounded-lg w-fit text-neutral-100 dark:bg-orange-500 dark:text-black">
                    RECOMMENDED
                </span>
                @endif --}}

                <h3 class="text-xl font-bold text-balance md:text-2xl text-neutral-900 dark:text-neutral-100">
                    {{ $plan->name }}
                </h3>


                <p class="mt-2 text-xs font-medium text-pretty">
                    {{ $plan->description ?? 'Best tools for your needs' }}
                </p>

                <span class="mt-8 text-3xl font-medium text-balance md:text-4xl text-neutral-600 dark:text-neutral-400">
                    ${{ number_format($plan->price, 2) }}
                </span>
                <span class="mt-2 text-xs font-medium text-pretty">Per month</span>

                <h4 class="mt-12 font-medium text-neutral-900 dark:text-neutral-100">Features</h4>
                <ul
                    class="mt-4 space-y-2 text-sm font-medium list-disc list-inside marker:text-lg marker:text-black dark:marker:text-orange-500">
                    {{-- @foreach($plan->features as $feature)
                    <li>{{ $feature->name }} ({{ $feature->pivot->charges }})</li>
                    @endforeach --}}
                    <li>Number of contacts ({{ (int)$plan->features[0]->pivot->charges }})</li>
                    <li>Emails per month ({{ (int)$plan->features[0]->pivot->charges }})</li>
                </ul>

                @auth
                <button type="button" @if($currentPlanId===$plan->id || ($currentPlanId && $plan->price <
                        $currentPlanPrice)) disabled
                        class="px-4 py-2 mt-12 w-full text-xs font-medium tracking-wide text-center text-white whitespace-nowrap rounded-lg transition cursor-not-allowed md:text-sm bg-neutral-400"
                        @else wire:click="$set('selectedPlan', {{ $plan->id }})" wire:loading.attr="disabled" @class([ 'mt-12 w-full whitespace-nowrap px-4 py-2 text-center text-sm font-medium tracking-wide transition
                    rounded-lg' , 'bg-black text-neutral-100 dark:bg-orange-500 dark:text-black'=> $selectedPlan !==
                        $plan->id,
                        'bg-green-500 text-white' => $selectedPlan === $plan->id,
                        ])
                        @endif>
                        @if($currentPlanId === $plan->id)
                        CURRENT
                        @elseif($currentPlanId && $plan->price < $currentPlanPrice) DOWNGRADE NOT ALLOWED @else {{
                            $selectedPlan===$plan->id ? 'Selected' : 'Select Plan' }}
                            @endif
                </button>
                @endauth
            </article>
            @endforeach
        </div>

        <!-- Yearly Plans -->
        <div x-cloak x-show="selectedTab === 'yearly'" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach($yearlyPlans as $plan)
            <article
                class="flex overflow-hidden flex-col p-3 w-full rounded-lg md:p-6 group bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                {{-- @if($loop->first)
                <span
                    class="px-2 py-1 ml-auto text-xs font-medium bg-black rounded-lg w-fit text-neutral-100 dark:bg-orange-500 dark:text-black">
                    BEST VALUE
                </span>
                @endif --}}

                <h3 class="text-xl font-bold text-balance md:text-2xl text-neutral-900 dark:text-neutral-100">
                    {{ $plan->name }}
                </h3>

                <p class="mt-2 text-xs font-medium text-pretty">
                    {{ $plan->description ?? 'Save with yearly billing' }}
                </p>

                <span class="mt-8 text-3xl font-medium text-balance md:text-4xl text-neutral-600 dark:text-neutral-400">
                    ${{ number_format($plan->price / 12, 2) }}
                </span>
                <span class="mt-2 text-xs font-medium text-pretty">
                    Per month, billed annually (${{ number_format($plan->price, 2) }}/year)
                </span>

                <h4 class="mt-12 font-medium text-neutral-900 dark:text-neutral-100">Features</h4>
                <ul
                    class="mt-4 space-y-2 text-sm font-medium list-disc list-inside marker:text-lg marker:text-black dark:marker:text-orange-500">
                    {{-- @foreach($plan->features as $feature)
                    <li>{{ $feature->name }} ({{ $feature->pivot->charges }})</li>
                    @endforeach --}}
                    <li>Number of contacts ({{ (int)$plan->features[0]->pivot->charges }})</li>
                    <li>Emails per month ({{ (int)$plan->features[0]->pivot->charges }})</li>
                </ul>

                @auth
                <button type="button" @if($currentPlanId===$plan->id || ($currentPlanId && $plan->price <
                        $currentPlanPrice)) disabled
                        class="px-4 py-2 mt-12 w-full text-xs font-medium tracking-wide text-center text-white whitespace-nowrap rounded-lg transition cursor-not-allowed md:text-sm bg-neutral-400"
                        @else wire:click="$set('selectedPlan', {{ $plan->id }})" wire:loading.attr="disabled" @class([ 'mt-12 w-full whitespace-nowrap px-4 py-2 text-center text-sm font-medium tracking-wide transition
                    rounded-lg' , 'bg-black text-neutral-100 dark:bg-orange-500 dark:text-black'=> $selectedPlan !==
                        $plan->id,
                        'bg-green-500 text-white' => $selectedPlan === $plan->id,
                        ])
                        @endif>
                        @if($currentPlanId === $plan->id)
                        CURRENT
                        @elseif($currentPlanId && $plan->price < $currentPlanPrice) DOWNGRADE NOT ALLOWED @else {{
                            $selectedPlan===$plan->id ? 'Selected' : 'Select Plan' }}
                            @endif
                </button>
                @endauth
            </article>
            @endforeach
        </div>
    </div>

    <!-- Payment Button -->
    @auth
    @if($selectedPlan)
    <div class="flex flex-col gap-4 items-center mt-8">
        @isset($upgradeCalculation)
        @if($upgradeCalculation['title']=="Upgrade" && $currentPlanId !== 1)
        <div
            class="p-4 w-full max-w-md text-sm rounded-lg bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
            <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-400">
                When upgrading to a higher plan, the cost is based on the difference between the new plan's price and
                what you've
                already paid, ensuring you only pay the remaining amount.
            </p>
            <h4 class="mb-2 font-medium text-neutral-900 dark:text-neutral-100">{{ $upgradeCalculation['title'] }} Cost
                Calculation</h4>
            <ul class="space-y-1">
                <li>Unused amount from current plan: ${{ $upgradeCalculation['unused_amount'] }}</li>
                <li>total Period Days: {{ $upgradeCalculation['totalPeriodDays'] }} days</li>
                <li>Remaining days: {{ $upgradeCalculation['remaining_days'] }} days</li>
                <li>New daily rate: ${{ $upgradeCalculation['new_daily_rate'] }}/day</li>
                <li>Old daily rate: ${{ $upgradeCalculation['current_daily_rate'] }}/day</li>
                <li>Start Date: ${{ $upgradeCalculation['will_started_at']->timezone($time_zone)->format('d/m/Y h:i:s A') }}</li>
                <li>Expiration Date: ${{ $upgradeCalculation['will_expired_at']->timezone($time_zone)->format('d/m/Y h:i:s A') }}</li>
                <li class="pt-2 mt-2 font-medium border-t border-neutral-200 dark:border-neutral-700">
                    payment required: ${{ $upgradeCalculation['upgrade_cost'] }}
                </li>
            </ul>
        </div>
        @endif
        @endisset

        <button wire:click="initiatePayment" wire:loading.attr="disabled"
            class="inline-flex justify-center items-center px-8 py-3 min-w-[200px] max-w-xs text-sm font-semibold text-white bg-black rounded-lg transition hover:bg-black/80 dark:bg-orange-500 dark:text-black dark:hover:bg-orange-600">
            <span wire:loading.remove>
                @if($upgradeCalculation && $currentPlanId !== 1)
                @if($upgradeCalculation['title'] === 'Upgrade')
                Pay ${{ $upgradeCalculation['upgrade_cost'] }} to Upgrade Plan
                @else
                Pay ${{ $upgradeCalculation['upgrade_cost'] }} to Subscribe
                @endif
                @else
                Pay ${{ $selectedPlan ? (collect($monthlyPlans)->merge($yearlyPlans))->firstWhere('id',
                $selectedPlan)->price : '0.00' }} to Subscribe
                @endif
            </span>
            <span wire:loading>Processing...</span>
        </button>
    </div>
    @endif
    @else
    <div class="flex justify-center mt-8">
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            Please <a href="{{ route('login') }}"
                class="font-medium text-black hover:underline dark:text-orange-500">sign in</a> to purchase a
            subscription
        </p>
    </div>
    @endauth





    <livewire:pages.user.subscription.payment-method-selection>




        <!-- Payment Modal -->
<div x-data="{
        openPaymentWindow(url) {
            const width = 500;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            window.open(
                url,
                'PayPal Payment - {{ config('app.name') }}',
                `width=${width},height=${height},left=${left},top=${top}`
            );
        }
    }" @paypalPayment.window="openPaymentWindow($event.detail.url)">

</div>