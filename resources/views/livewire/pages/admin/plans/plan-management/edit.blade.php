<div
    class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
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
        <div class="p-6 border rounded-md border-neutral-300 dark:border-neutral-700">
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
        <div class="p-6 border rounded-md border-neutral-300 dark:border-neutral-700">
            <h3 class="mb-4 text-lg font-medium">Features Management</h3>

            <!-- Current Features -->
            <div class="mb-6">
                <h4 class="mb-2 text-sm font-medium">Current Features</h4>
                <div class="space-y-2">
                    @foreach($features as $featureId => $feature)
                    <div class="flex items-center justify-between p-3 border rounded-md border-neutral-300 dark:border-neutral-700">
                        <span class="font-medium">{{ $feature['name'] }}</span>
                        <div class="flex items-center space-x-2">
                            <x-text-input type="number" wire:model.live="features.{{ $featureId }}.limit" class="w-24"
                                placeholder="Limit" />
                            <x-primary-button wire:click="updateFeatureLimit({{ $featureId }})" size="sm">
                                Update
                            </x-primary-button>
                            <x-danger-button wire:click="detachFeature({{ $featureId }})" size="sm">
                                Remove
                            </x-danger-button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Add Feature -->
            @if(count($availableFeatures) > 0)
            <div class="p-4 border rounded-md border-neutral-300 dark:border-neutral-700">
                <h4 class="mb-4 text-sm font-medium">Add Feature</h4>
                <form wire:submit.prevent="attachFeature" class="space-y-4">
                    <div>
                        <x-input-label for="selectedFeature" :value="__('Select Feature')" />
                        <x-primary-select-input wire:model="selectedFeature" id="selectedFeature">
                            <option value="">Select a feature</option>
                            @foreach($availableFeatures as $feature)
                            <option value="{{ $feature['id'] }}">{{ $feature['name'] }}</option>
                            @endforeach
                        </x-primary-select-input>
                    </div>

                    <div>
                        <x-input-label for="featureLimit" :value="__('Feature Limit')" />
                        <x-text-input wire:model="featureLimit" id="featureLimit" type="number"
                            class="block w-full mt-1" placeholder="Enter limit" />
                        <x-input-error :messages="$errors->get('featureLimit')" class="mt-2" />
                    </div>

                    <x-primary-button type="submit">
                        Add Feature
                    </x-primary-button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
