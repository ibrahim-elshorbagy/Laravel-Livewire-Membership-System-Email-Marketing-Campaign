<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $repeaterId ? 'Edit Repeater' : 'New Repeater' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-link href="{{ route('user.campaigns.repeaters.list') }}" wire:navigate>
                Back to Repeaters
            </x-primary-info-link>
        </div>
    </header>

    <div
        class="p-4 mb-4 text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-300/10 dark:text-blue-300">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-question"></i>
            <span class="font-medium">Campaign Repeater Information</span>
        </div>

        <div class="mt-2 text-sm">
            <p>You are setting up a repeater for: "<strong>{{ $campaign->title }}</strong>"</p>
            <p class="mt-1"><strong>Important Note:</strong> If you set a repeater to run 3 times, you will have 3 total campaigns (including the original one). So it will create 2 additional campaigns.</p>
            <p class="mt-1">- Each new campaign will be created after the previous one completes, with a waiting
                period based on your interval setting.</p>
            <p class="mt-1"> - The new campaign will use the same sending bots.</p>
        </div>
    </div>

    <form wire:submit.prevent="saveRepeater" class="space-y-6">
        <!-- Interval -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="intervalValue" required>Interval Value</x-input-label>
                <x-text-input wire:model.live="intervalValue" id="intervalValue" type="number" min="1"
                    max="365" class="block w-full mt-1" required />
                <x-input-error :messages="$errors->get('intervalValue')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="intervalType" required>Interval Type</x-input-label>
                <x-primary-select-input wire:model.live="intervalType" id="intervalType" class="block w-full mt-1"
                    required>
                    <option value="hours">Hours</option>
                    <option value="days">Days</option>
                    <option value="weeks">Weeks</option>
                </x-primary-select-input>
                <x-input-error :messages="$errors->get('intervalType')" class="mt-2" />
            </div>
        </div>

        <!-- Number of repeats -->
        <div>
            <x-input-label for="totalRepeats" required>Number of Repeats</x-input-label>
            <x-text-input wire:model.live="totalRepeats" id="totalRepeats" type="number" min="1" max="50"
                class="block w-full mt-1" required />
            @php
                $safeTotalRepeats = is_numeric($totalRepeats) && $totalRepeats > 0 ? $totalRepeats : 1;
            @endphp
            <p class="mt-1 text-sm text-gray-500">
                This will run the campaign {{ $safeTotalRepeats }} time(s) total (original + {{ $safeTotalRepeats - 1 }} repeats).
            </p>

            @if($repeaterModel)

                @if($repeaterModel?->completed_repeats > 0)
                <div class="p-3 mt-2 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/10 dark:border-green-300/10">
                    <div class="flex items-center gap-2 text-green-800 dark:text-green-300">
                        <i class="fa-solid fa-check-circle"></i>
                        <span class="font-medium">Current Progress</span>
                    </div>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                        {{ $repeaterModel->completed_repeats }} out of {{ $repeaterModel->total_repeats }} repeats completed.
                        @if($repeaterModel->completed_repeats < $repeaterModel->total_repeats)
                            {{ $repeaterModel->total_repeats - $repeaterModel->completed_repeats }} more repeat(s) remaining.
                        @else
                            All repeats completed!
                        @endif
                    </p>
                </div>
                @endif
            @endif

            <x-input-error :messages="$errors->get('totalRepeats')" class="mt-2" />
        </div>

        <!-- Activate Repeater -->
        <div class="flex items-center">
            <input id="active" type="checkbox" wire:model.live="active"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:focus:ring-blue-600">
            <label for="active" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                Activate repeater immediately
            </label>
        </div>

        <!-- Summary -->
        <div
            class="p-4 mb-4 text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-300/10 dark:text-blue-300">
            <h3 class="text-lg font-medium">Summary</h3>
            <div class="mt-2 text-sm">
                <p>
                    - This will create a repeater that will recreate the campaign "<strong>{{ $campaign->title }}</strong>"
                    every <strong>{{ $intervalValue }} {{ $intervalType }}</strong>
                    after the previous campaign completes,
                    for a total of <strong>{{ $totalRepeats }}</strong> time(s).
                </p>
                <p class="mt-2">
                    - The repeater will be <strong>{{ $active ? 'active' : 'inactive' }}</strong> upon creation.
                </p>
            </div>
        </div>


        <div class="flex justify-end space-x-3">
            <x-secondary-button type="button" wire:navigate href="{{ route('user.campaigns.repeaters.list') }}">
                Cancel
            </x-secondary-button>
            <x-primary-create-button type="submit">
                {{ $repeaterId ? 'Update Repeater' : 'Create Repeater' }}
            </x-primary-create-button>
        </div>
    </form>
</div>
