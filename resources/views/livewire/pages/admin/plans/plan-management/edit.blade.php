<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Header -->
    <div class="mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                Edit Plan: {{ $plan->name }}
            </h2>
        </div>
        <div class="flex mt-4 md:mt-0 md:ml-4">
            <x-primary-info-button href="{{ route('admin.plans') }}" wire:navigate>
                Back to Plans
            </x-primary-info-button>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Plan Details -->
        <div class="p-4 border rounded-md border-neutral-300 dark:border-neutral-700">
            <h3 class="mb-4 text-lg font-medium">Plan Details</h3>
            <form wire:submit.prevent="updatePlan" class="space-y-4">
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Plan Name')" />
                    <x-text-input wire:model="name" id="name" type="text" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Price -->
                <div>
                    <x-input-label for="price" :value="__('Price (USD)')" />
                    <x-text-input wire:model="price" id="price" type="number" step="0.01" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>

                <div class="pt-4">
                    <x-primary-create-button type="submit">
                        Update Plan
                    </x-primary-create-button>
                </div>
            </form>
        </div>

        <!-- Features Management -->
        <div class="p-4 border rounded-md border-neutral-300 dark:border-neutral-700 sm:p-6">
            <h3 class="mb-4 text-lg font-medium">Features Management</h3>
            <div class="space-y-4">
                @foreach($features as $feature)
                <div
                    class="flex items-center justify-between p-3 border rounded-md border-neutral-300 dark:border-neutral-700">
                    <x-input-label :value="$feature->name" class="mb-0" />
                    <div class="w-32">
                        <x-text-input wire:model="featureLimits.{{ $feature->id }}" type="number" min="0"
                            placeholder="Limit" class="text-right" step="1" />
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
