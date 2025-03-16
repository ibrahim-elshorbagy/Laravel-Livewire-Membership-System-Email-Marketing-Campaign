<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
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
            <x-primary-info-button href="{{ route('user.campaigns.form') }}" wire:navigate>
                New Campaign
            </x-primary-info-button>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search campaigns..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
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
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4 w-8">#</th>
                    <th scope="col" class="p-4 w-48">Message</th>
                    <th scope="col" class="p-4 w-64">Servers</th>
                    <th scope="col" class="p-4 w-64">Email Lists</th>
                    <th scope="col" class="p-4 w-48">Progress</th>
                    <th scope="col" class="p-4 w-48">Created At</th>
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
                                {{ $campaign->message->message_title }}
                            </span>
                            <span class="text-xs text-neutral-600 dark:text-neutral-400">
                                {{ $campaign->message->email_subject }}
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1 text-nowrap">
                            @foreach($campaign->servers as $server)
                            <span class="px-2 py-1 text-xs text-blue-500 rounded-full bg-blue-500/10">
                                {{ $server->name }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1 text-nowrap">
                            @foreach($campaign->emailLists as $list)
                            <a href="{{ route('user.emails.index') }}?selectedList={{ $list->name }}">
                                <span class="px-2 py-1 text-xs text-green-500 rounded-full bg-green-500/10">
                                    {{ $list->name }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </td>
                    <td class="p-4 text-nowrap">
                        @php
                        $totalEmails = $campaign->emailLists->flatMap(function($list) {
                        return $list->emails;
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
                    <td class="p-4 text-nowrap">{{ $campaign->created_at->format('d/m/Y h:i A') }}</td>
                    <td class="p-4">
                        <div class="flex space-x-2">



                            <div class="flex items-center space-x-2">
                                <div class="relative" x-data="{ showTooltip: false }">
                                    <button wire:click="toggleActive({{ $campaign->id }})"
                                        @if(!$campaign->canBeModified()) disabled @endif
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
                                    @if(!$campaign->canBeActive())
                                    <!-- Tooltip -->
                                    <div x-show="showTooltip" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute bottom-full left-1/2 z-10 px-3 py-2 mb-2 w-max text-sm text-white rounded-lg shadow-lg -translate-x-1/2 bg-neutral-900"
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
                                            class="absolute top-full left-1/2 w-0 h-0 border-t-8 border-r-8 border-l-8 -translate-x-1/2 border-l-transparent border-r-transparent border-neutral-900">
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- <a href="{{ route('user.campaigns.progress', $campaign) }}" wire:navigate
                                    class="inline-flex items-center px-2 py-1 text-xs text-purple-500 rounded-md bg-purple-900/10 hover:bg-purple-500/20">
                                    <i class="mr-1 fas fa-chart-line"></i> Progress
                                </a> --}}

                                @if($campaign->status != 'Completed')
                                <a href="{{ route('user.campaigns.form', $campaign->id) }}" wire:navigate
                                    class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                    <i class="mr-1 fas fa-edit"></i>
                                </a>
                                @endif

                                <button wire:click="deleteCampaign({{ $campaign->id }})"
                                    wire:confirm="Are you sure you want to delete this campaign?"
                                    class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                                    <i class="mr-1 fas fa-trash"></i>
                                </button>
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