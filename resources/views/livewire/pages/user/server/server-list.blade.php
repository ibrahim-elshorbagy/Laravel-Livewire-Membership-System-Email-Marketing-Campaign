<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            My Sending bots
        </h2>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search servers..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="name">Sort by Name</option>
                    <option value="last_access_time">Sort by Last Access</option>
                    <option value="current_quota">Sort by Quota</option>
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
                    <th scope="col" class="p-4">Name</th>
                    <th scope="col" class="p-4">Campaign</th>
                    <th scope="col" class="p-4">Last Access</th>
                    <th scope="col" class="p-4">Quota</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($servers as $server)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        @if ($server->is_orphan)
                        <span class="text-lg text-red-500">
                            *
                        </span>
                        @endif
                        {{ $server->name }}
                    </td>
                    <td class="p-4">{{ $server->campaign }}</td>
                    <td class="p-4">{{ $server->last_access_time?->timezone(auth()->user()->timezone ??
                        $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i:s A') ?? '' }}</td>
                    <td class="p-4">
                        <span
                            class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $server->current_quota > 50 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $server->current_quota }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @if($servers->isEmpty())
    <div class="flex flex-col justify-center items-center p-6 text-center">
        <div class="p-3 mb-4 rounded-full bg-neutral-100 dark:bg-neutral-800">
            <svg class="w-6 h-6 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
            </svg>
        </div>
        <h3 class="mb-1 text-lg font-medium text-neutral-900 dark:text-neutral-100">No Servers Found</h3>
        <p class="text-neutral-500 dark:text-neutral-400">You don't have any servers assigned to you yet.</p>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-4">
        {{ $servers->links() }}
    </div>
</div>
