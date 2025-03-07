<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            API Requests
        </h2>
        <div class="mt-4 md:mt-0">
            <button wire:click="deleteAll" wire:confirm="Are you sure you want to delete all API requests? This action cannot be undone."
                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                Delete All Requests
            </button>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search ServerId..." class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="serverid">Sort by Server ID</option>
                    <option value="request_time">Sort by Date</option>
                    <option value="execution_time">Sort by Duration</option>
                    <option value="status">Sort by Status</option>
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

    <!-- Bulk Actions -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
            @if(count($selectedRequests) > 0)
            <span class="text-sm font-medium">{{ count($selectedRequests) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected requests?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">Server ID</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Duration</th>
                    <th scope="col" class="p-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($requests as $request)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedRequests" value="{{ $request->id }}" class="rounded">
                    </td>
                    <td class="p-4">{{ $request->serverid }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $request->status === 'success' ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="p-4">{{ number_format($request->execution_time, 3) }}s</td>
                    <td class="p-4">{{ $request->request_time->format('d/m/Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @if($requests->isEmpty())
    <div class="flex flex-col justify-center items-center p-6 text-center">
        <h3 class="mb-1 text-lg font-medium text-neutral-900 dark:text-neutral-100">No API Requests Found</h3>
        <p class="text-neutral-500 dark:text-neutral-400">There are no API requests recorded in the system.</p>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>
