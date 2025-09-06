<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            My Campaigns
        </h2>
        <div class="flex mt-4 space-x-2 md:mt-0">
            <x-primary-info-button wire:click="$refresh" x-data="{ spinning: false }"
                @click="spinning = true; setTimeout(() => spinning = false, 1000)">
                <div class='flex items-start'>
                    <i class="mr-1 mt-.5 fas fa-sync-alt" :class="{ 'animate-spin': spinning }"></i>
                    Refresh
                </div>

            </x-primary-info-button>
            <x-primary-info-link href="{{ route('user.campaigns.repeaters.list') }}" wire:navigate>
                <div class='flex items-center'>
                    <i class="mr-1 fas fa-clock"></i>
                    Repeaters
                </div>
            </x-primary-info-link>
            <x-primary-info-link href="{{ route('user.campaigns.form') }}" wire:navigate>
                New Campaign
            </x-primary-info-link>
        </div>
    </header>

    @php
    $unusedServers = $availableServers->filter(function($server) { return !$server->is_used; });
    $displayServers = $unusedServers->take(8);
    $remainingCount = $unusedServers->count() - 8;
    @endphp
    @if(!$unusedServers->isEmpty())

        <x-primary-accordion title="Available Bots" :isExpandedByDefault="false">
            <div class="overflow-x-auto">
                <div
                    class="p-4 mb-4 text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-300/10 dark:text-blue-300">
                    <div class="flex items-center gap-2">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12 1.5c-1.921 0-3.816.111-5.68.327-1.497.174-2.57 1.46-2.57 2.93V21.75a.75.75 0 001.029.696l3.471-1.388 3.472 1.388a.75.75 0 00.556 0l3.472-1.388 3.471 1.388a.75.75 0 001.029-.696V4.757c0-1.47-1.073-2.756-2.57-2.93A49.255 49.255 0 0012 1.5zm3.53 7.28a.75.75 0 00-1.06-1.06l-2.47 2.47-.97-.97a.75.75 0 00-1.06 1.06l1.5 1.5a.75.75 0 001.06 0l3-3z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Available Bots</span>
                    </div>

                    <div class="mt-2 text-sm">
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">

                            @foreach($displayServers as $server)
                            <div class="flex items-center p-2 space-x-2 rounded-lg bg-blue-100/50 dark:bg-blue-900/50">
                                <i class="fas fa-robot"></i>
                                <span>{{ $server->name }}</span>
                            </div>
                            @endforeach
                            @if($remainingCount > 0)
                            <div class="flex items-center p-2 space-x-2 rounded-lg bg-blue-100/50 dark:bg-blue-900/50">
                                <i class="fas fa-ellipsis-h"></i>
                                <a href="{{ route('user.servers') }}" wire:navigate>+{{ $remainingCount }} more <i class="text-xs fa-solid fa-arrow-right fa-fade"></i></span></a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-primary-accordion>

    @endif

    <!-- Search and Filters -->
    <div class="my-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search campaigns..."
                    class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="title">Sort by Title</option>
                    <option value="created_at">Sort by Date</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="statusFilter" class="w-full sm:w-48">
                    <option value="">All Status</option>
                    <option value="Sending">Sending</option>
                    <option value="Pause">Pause</option>
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
                    <th scope="col" class="w-48 p-4">Message</th>
                    <th scope="col" class="w-64 p-4">Sending bots</th>
                    <th scope="col" class="w-64 p-4">Email Lists</th>
                    <th scope="col" class="w-48 p-4">Progress</th>
                    <th scope="col" class="w-48 p-4">Created At</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($campaigns as $index => $campaign)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4 text-nowrap">{{ $campaigns->firstItem() + $index }}</td>
                    <td class="p-4 text-nowrap">
                        <div class="flex flex-col">
                            <span class="font-medium text-neutral-800 dark:text-neutral-200">
                                {{ $campaign->title }}
                            </span>
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ Str::limit($campaign->message->message_title, 25) }}
                            </span>
                            <span class="text-xs text-neutral-600 dark:text-neutral-400">
                                {{ Str::limit($campaign->message->email_subject, 25) }}
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1 text-nowrap">
                            @foreach($campaign->servers->take(4) as $server)
                            <span class="px-2 py-1 text-xs text-blue-500 rounded-full bg-blue-500/10">
                                {{ $server->name }}
                            </span>
                            @endforeach
                            @if($campaign->servers->count() > 4)
                            <span class="px-2 py-1 text-xs text-blue-500 rounded-full bg-blue-500/10">
                                +{{ $campaign->servers->count() - 4 }} more
                            </span>
                            @endif
                            @if($campaign->servers->count() == 0)
                            @if($campaign->status != 'Completed')
                            No servers selected, edit to add servers
                            @endif
                            @endif
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1 text-nowrap">
                            @foreach($campaign->emailLists->take(4) as $list)
                            <a href="{{ route('user.emails.index') }}?selectedList={{ $list->name }}">
                                <span class="px-2 py-1 text-xs text-green-500 rounded-full bg-green-500/10">
                                    {{ $list->name }}
                                </span>
                            </a>
                            @endforeach
                            @if($campaign->emailLists->count() > 4)
                            <span class="px-2 py-1 text-xs text-green-500 rounded-full bg-green-500/10">
                                +{{ $campaign->emailLists->count() - 4 }} more
                            </span>
                            @endif
                            @if($campaign->emailLists->count() == 0)
                            @if($campaign->status != 'Completed')
                            No email lists selected, edit to add lists
                            @endif
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-nowrap">
                        @php
                        $totalEmails = $campaign->emailLists->flatMap(function($list) {
                        return $list->emails->where('is_hard_bounce', false);
                        })->count();
                        $sentEmails = $campaign->emailHistories()->where('status', 'sent')->count();
                        $percentage = $totalEmails > 0 ? round(($sentEmails / $totalEmails) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('user.campaigns.progress', $campaign) }}" wire:navigate>
                                <span
                                    class="px-2 py-1 text-xs rounded-full {{ $percentage == 100 ? 'bg-green-500/10 text-green-500' : 'bg-blue-500/10 text-blue-500' }}">
                                    {{ $percentage }}% ({{ $sentEmails }}/{{ $totalEmails }})
                                </span>
                            </a>
                            <button wire:click="$refresh" x-data="{ spinning: false }"
                                @click="spinning = true; setTimeout(() => spinning = false, 1000)"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="text-xs fas fa-sync-alt" :class="{ 'animate-spin': spinning }"></i>
                            </button>
                        </div>
                    </td>
                    <td class="p-4 text-nowrap">
                            {{ $campaign->created_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i A') }}
                          @if ($campaign?->repeater?->next_run_at)
                            <br>
                            Next Run
                            <br>
                            {{ $campaign?->repeater?->next_run_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i A') }}
                            <br>
                            {{ $campaign?->repeater?->next_run_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->diffForHumans()  }}
                          @endif
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <div class="flex items-center space-x-2">
                                <div class="relative" x-data="{ showTooltip: false }">
                                    <button wire:click="toggleActive({{ $campaign->id }})"
                                        @if(!$campaign->canBeModified()) disabled @endif
                                        @mouseenter="showTooltip = true"
                                        @mouseleave="showTooltip = false"
                                        class="inline-flex items-center px-2 py-1 text-xs rounded-md
                                        {{ $campaign->status === 'Sending' ? 'bg-green-500/10 text-green-500' :
                                        ($campaign->status === 'Completed' ? 'bg-blue-500/10 text-blue-500' :
                                        'bg-gray-500/10 text-gray-500') }}
                                        {{ !$campaign->canBeActive() || !$campaign->canBeModified() ? 'opacity-50
                                        cursor-not-allowed' :
                                        'hover:bg-opacity-20' }}">
                                        <i class="mr-1 fas {{
                                            $campaign->status === 'Sending' ? 'fa-play-circle' :
                                            ($campaign->status === 'Completed' ? 'fa-check-circle' : 'fa-pause-circle')
                                        }}"></i>
                                        {{ $campaign->status }}
                                    </button>

                                    @if($campaign->status === 'Completed')
                                    <!-- Tooltip for completed campaigns -->
                                    <div x-show="showTooltip" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 px-3 py-2 mb-2 text-sm text-white -translate-x-1/2 rounded-lg shadow-lg bottom-full left-1/2 w-max bg-neutral-900"
                                        role="tooltip">
                                        <div class="flex items-center space-x-1">
                                            <i class="text-blue-500 fas fa-check-circle"></i>
                                            <span>Campaign has been completed</span>
                                        </div>
                                        <!-- Arrow -->
                                        <div
                                            class="absolute w-0 h-0 -translate-x-1/2 border-t-8 border-l-8 border-r-8 top-full left-1/2 border-l-transparent border-r-transparent border-neutral-900">
                                        </div>
                                    </div>
                                    @elseif(!$campaign->canBeActive())
                                    <!-- Tooltip for when campaign cannot be started -->
                                    <div x-show="showTooltip" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 px-3 py-2 mb-2 text-sm text-white -translate-x-1/2 rounded-lg shadow-lg bottom-full left-1/2 w-max bg-neutral-900"
                                        role="tooltip">
                                        <div class="flex items-center space-x-1">
                                            <i class="text-yellow-500 fas fa-exclamation-triangle"></i>
                                            <span>
                                                @if($campaign->servers()->count() === 0 &&
                                                $campaign->emailLists()->count() === 0)
                                                No servers and email lists assigned
                                                @elseif($campaign->servers()->count() === 0)
                                                No servers assigned
                                                @else
                                                No email lists assigned
                                                @endif
                                            </span>
                                        </div>
                                        <!-- Arrow -->
                                        <div
                                            class="absolute w-0 h-0 -translate-x-1/2 border-t-8 border-l-8 border-r-8 top-full left-1/2 border-l-transparent border-r-transparent border-neutral-900">
                                        </div>
                                    </div>
                                    @elseif($campaign->status != 'Sending' && $campaign->status != 'Completed')
                                    <!-- Tooltip for when campaign can be started -->
                                    <div x-show="showTooltip" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 px-3 py-2 mb-2 text-sm text-white -translate-x-1/2 rounded-lg shadow-lg bottom-full left-1/2 w-max bg-neutral-900"
                                        role="tooltip">
                                        <div class="flex items-center space-x-1">
                                            <i class="text-green-500 fas fa-play-circle"></i>
                                            <span>Unpause to start sending</span>
                                        </div>
                                        <!-- Arrow -->
                                        <div
                                            class="absolute w-0 h-0 -translate-x-1/2 border-t-8 border-l-8 border-r-8 top-full left-1/2 border-l-transparent border-r-transparent border-neutral-900">
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                @if($campaign->status != 'Completed')
                                <a href="{{ route('user.campaigns.form', $campaign->id) }}" wire:navigate
                                    class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                    <i class="mr-1 fas fa-edit"></i>
                                </a>
                                @endif

                                <!-- Show repeater button only for non-completed campaigns -->
                                @if($campaign->status != 'Completed')
                                <a href="{{ route('user.campaigns.repeaters.campaign.form', ['campaign' => $campaign->id]) }}" wire:navigate
                                    class="inline-flex items-center px-2 py-1 text-xs text-purple-500 rounded-md bg-purple-500/10 hover:bg-purple-500/20">
                                    <i class="mr-1 fas fa-clock"></i>
                                </a>
                                @endif

                                <button wire:click="deleteCampaign({{ $campaign->id }})"
                                    wire:confirm="Are you sure you want to delete this campaign? It will also delete all associated email histories. And It's Repeater "
                                    class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                                    <i class="mr-1 fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $campaigns->links() }}
    </div>
</div>
