<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Campaign Repeaters
        </h2>
        <div class="flex mt-4 space-x-2 md:mt-0">
            <x-primary-info-button wire:click="$refresh" x-data="{ spinning: false }"
                @click="spinning = true; setTimeout(() => spinning = false, 1000)">
                <div class='flex items-start'>
                    <i class="mr-1 mt-.5 fas fa-sync-alt" :class="{ 'animate-spin': spinning }"></i>
                    Refresh
                </div>
            </x-primary-info-button>
            <x-primary-info-link href="{{ route('user.campaigns.list') }}" wire:navigate>
                Campaigns
            </x-primary-info-link>
        </div>
    </header>

    <div
        class="p-4 mb-4 text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-300/10 dark:text-blue-300">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-question"></i>
            <span class="font-medium">Campaign Repeaters</span>
        </div>

        <div class="mt-2 text-sm">
            <p>Campaign repeaters allow you to automatically create new campaigns with the same settings after the previous campaign completes.</p>
            <p class="mt-1"><strong>Note:</strong> If you set a repeater to run 3 times, you will have 3 campaigns total (including the original one). So it will create 2 additional campaigns.</p>
            <p class="mt-1"> - Each new campaign will be created after the previous one completes, with a waiting period based on your interval setting.</p>
            <p class="mt-1"> - The new campaign will use the same sending bots.</p>
            <p class="mt-1"> - You can't create repeater for a completed campaign.</p>

        </div>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search repeaters..."
                    class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="created_at">Sort by Date</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="statusFilter" class="w-full sm:w-48">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Completed">Completed</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="sortDirection" class="w-full sm:w-32">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="perPage" class="w-full sm:w-32">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </x-primary-select-input>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="w-8 p-4">#</th>
                    <th scope="col" class="w-48 p-4">Campaign</th>
                    <th scope="col" class="p-4">Interval</th>
                    <th scope="col" class="p-4">Next Run At</th>
                    <th scope="col" class="p-4">Repeats</th>
                    <th scope="col" class="p-4">Created At</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @forelse($repeaters as $index => $repeater)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4 text-nowrap">{{ $repeaters->firstItem() + $index }}</td>
                    <td class="p-4 text-nowrap">
                        <div class="flex flex-col">
                            <a href="{{ route('user.campaigns.form', $repeater->campaign_id) }}" wire:navigate
                                class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ $repeater->campaign->title }}
                            </a>
                        </div>
                    </td>
                    <td class="p-4">
                        @php
                        $interval = $repeater->interval_hours;
                        $unit = 'hour';

                        if ($repeater->interval_type === 'days') {
                            $interval = $interval / 24;
                            $unit = 'day';
                        } elseif ($repeater->interval_type === 'weeks') {
                            $interval = $interval / (24 * 7);
                            $unit = 'week';
                        }

                        if ($interval != 1) {
                            $unit .= 's';
                        }
                        @endphp
                        <span class="px-2 py-1 text-xs text-purple-500 rounded-full bg-purple-500/10">
                            {{ $interval }} {{ $unit }}
                        </span>
                    </td>
                    <td class="p-4 text-nowrap">
                            {{ $repeater->next_run_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i A') }} -
                            {{ $repeater->next_run_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->diffForHumans() }}
                    </td>
                    <td class="p-4 text-nowrap">
                        <span class="px-2 py-1 text-xs text-blue-500 rounded-full bg-blue-500/10">
                            {{ $repeater->completed_repeats }}/{{ $repeater->total_repeats }}
                        </span>
                    </td>
                    <td class="p-4 text-nowrap">
                            {{ $repeater->created_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i A') }}
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            @if($repeater->completed_repeats < $repeater->total_repeats)
                            <button wire:click="toggleActive({{ $repeater->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-md
                                {{ $repeater->active ? 'bg-green-500/10 text-green-500' : 'bg-gray-500/10 text-gray-500' }}
                                hover:bg-opacity-20">
                                <i class="mr-1 fas {{ $repeater->active ? 'fa-play-circle' : 'fa-pause-circle' }}"></i>
                                {{ $repeater->active ? 'Active' : 'Inactive' }}
                            </button>

                            <a href="{{ route('user.campaigns.repeaters.form', ['repeater' => $repeater->id]) }}" wire:navigate
                                class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                <i class="mr-1 fas fa-edit"></i>
                                Edit
                            </a>
                            @endif


                            <button wire:click="deleteRepeater({{ $repeater->id }})"
                                wire:confirm="Are you sure you want to delete this repeater?"
                                class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                                <i class="mr-1 fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center">
                        <div class="flex flex-col items-center justify-center py-6">

                            <i class="fa-regular fa-clock w-12 h-12 text-gray-400 text-[48px]"></i>
                            <p class="mt-2 text-gray-500">No repeaters found</p>
                            <p class="mt-1 text-sm text-gray-400">Create a repeater from a campaign page</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $repeaters->links() }}
    </div>
</div>
