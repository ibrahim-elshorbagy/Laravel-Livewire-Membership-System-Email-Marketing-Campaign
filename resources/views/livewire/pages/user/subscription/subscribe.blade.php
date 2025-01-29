<div class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Processing Overlay -->
    {{-- <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="p-6 bg-white rounded-lg shadow-xl dark:bg-neutral-800">
            <div class="w-12 h-12 mx-auto border-b-2 rounded-full animate-spin border-primary-500"></div>
            <p class="mt-4 text-center dark:text-neutral-200">Processing your request...</p>
        </div>
    </div> --}}

    <!-- Subscription Plans -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab Navigation -->
        <div class="flex gap-2 mb-6 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
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
        <div x-show="selectedTab === 'monthly'" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($monthlyPlans as $plan)
            <article
                class="flex flex-col w-full p-3 overflow-hidden rounded-lg md:p-6 group bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
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
                    @foreach($plan->features as $feature)
                    <li>{{ $feature->name }} ({{ $feature->pivot->charges }})</li>
                    @endforeach
                </ul>

                <button type="button" wire:click="$set('selectedPlan', {{ $plan->id }})" wire:loading.attr="disabled"
                    @class([ 'mt-12 w-full whitespace-nowrap px-4 py-2 text-center text-sm font-medium tracking-wide transition rounded-lg'
                    , 'bg-black text-neutral-100 dark:bg-orange-500 dark:text-black'=> $selectedPlan !== $plan->id,
                    'bg-green-500 text-white' => $selectedPlan === $plan->id,
                    ])>
                    {{ $selectedPlan === $plan->id ? 'Selected' : 'Select Plan' }}
                </button>
            </article>
            @endforeach
        </div>

        <!-- Yearly Plans -->
        <div x-show="selectedTab === 'yearly'" class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($yearlyPlans as $plan)
            <article
                class="flex flex-col w-full p-3 overflow-hidden rounded-lg md:p-6 group bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
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
                    @foreach($plan->features as $feature)
                    <li>{{ $feature->name }} ({{ $feature->pivot->charges }})</li>
                    @endforeach
                </ul>

                <button type="button" wire:click="$set('selectedPlan', {{ $plan->id }})" wire:loading.attr="disabled"
                    @class([ 'mt-12 w-full whitespace-nowrap px-4 py-2 text-center text-sm font-medium tracking-wide transition rounded-lg'
                    , 'bg-black text-neutral-100 dark:bg-orange-500 dark:text-black'=> $selectedPlan !== $plan->id,
                    'bg-green-500 text-white' => $selectedPlan === $plan->id,
                    ])>
                    {{ $selectedPlan === $plan->id ? 'Selected' : 'Select Plan' }}
                </button>
            </article>
            @endforeach
        </div>
    </div>

    <!-- Payment Button -->
    @if($selectedPlan)
    <div class="flex justify-center mt-8">
        <button wire:click="initiatePayment" wire:loading.attr="disabled"
            class="px-6 py-3 text-sm font-semibold text-white transition bg-black rounded-lg hover:bg-black/80 dark:bg-orange-500 dark:text-black dark:hover:bg-orange-600">
            <span wire:loading.remove>Proceed to Payment</span>
            <span wire:loading>Processing...</span>
        </button>
    </div>
    @endif

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
    }" @payment-url.window="openPaymentWindow($event.detail.url)">

    @if($errors->any())
    <div class="p-4 mt-4 bg-red-100 rounded-md dark:bg-red-900/50">
        <ul class="text-red-600 list-disc list-inside dark:text-red-400">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

 

