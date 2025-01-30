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

                <!-- Billing Cycle -->
                {{-- <div>
                    <x-input-label for="periodicity" :value="__('Billing Cycle')" />
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-text-input wire:model="periodicity" id="periodicity" type="number"
                                class="block w-full mt-1" />
                            <x-input-error :messages="$errors->get('periodicity')" class="mt-2" />
                        </div>
                        <div>
                            <x-primary-select-input wire:model="periodicity_type" id="periodicity_type">
                                <option value="month">Month(s)</option>
                                <option value="year">Year(s)</option>
                            </x-primary-select-input>
                        </div>
                    </div>
                </div> --}}

                <div class="pt-4">
                    <x-primary-button type="submit">
                        Update Plan
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Features Management -->
        <div class="p-4 border rounded-md border-neutral-300 dark:border-neutral-700 sm:p-6">
            <h3 class="mb-4 text-lg font-medium">Features Management</h3>

            <!-- Current Features -->
            <div class="mb-6">
                <h4 class="mb-2 text-sm font-medium sm:text-base">Current Features</h4>
                <div class="space-y-3">
                    @foreach($features as $featureId => $feature)
                    <div
                        class="flex flex-col p-2 border rounded-md border-neutral-300 dark:border-neutral-700 sm:p-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="mb-2 text-sm font-medium sm:text-base sm:mb-0 sm:flex-1">{{ $feature['name'] }}</span>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                            <x-text-input type="number" wire:model.live="features.{{ $featureId }}.limit" class="w-full sm:w-24"
                                placeholder="Limit" />
                            <div class="flex gap-2">
                                <x-primary-button wire:click="updateFeatureLimit({{ $featureId }})"
                                    class="justify-center w-full sm:w-auto" size="sm">
                                    Update
                                </x-primary-button>
                                <x-danger-button wire:click="detachFeature({{ $featureId }})"
                                    class="justify-center w-full sm:w-auto" size="sm">
                                    Remove
                                </x-danger-button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Add Feature -->
            @if(count($availableFeatures) > 0)
            <div class="p-3 border rounded-md border-neutral-300 dark:border-neutral-700 sm:p-4">
                <h4 class="mb-3 text-sm font-medium sm:text-base">Add Feature</h4>
                <form wire:submit.prevent="attachFeature" class="space-y-3">
                    <div>
                        <x-input-label for="selectedFeature" :value="__('Select Feature')" class="text-sm sm:text-base" />
                        <x-primary-select-input wire:model="selectedFeature" id="selectedFeature"
                            class="w-full mt-1 text-sm sm:text-base">
                            <option value="">Select a feature</option>
                            @foreach($availableFeatures as $feature)
                            <option value="{{ $feature['id'] }}">{{ $feature['name'] }}</option>
                            @endforeach
                        </x-primary-select-input>
                    </div>

                    <div>
                        <x-input-label for="featureLimit" :value="__('Feature Limit')" class="text-sm sm:text-base" />
                        <x-text-input wire:model="featureLimit" id="featureLimit" type="number"
                            class="block w-full mt-1 text-sm sm:text-base" placeholder="Enter limit" />
                        <x-input-error :messages="$errors->get('featureLimit')" class="mt-2 text-sm" />
                    </div>

                    <x-primary-button type="submit" class="w-full sm:w-auto">
                        Add Feature
                    </x-primary-button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
