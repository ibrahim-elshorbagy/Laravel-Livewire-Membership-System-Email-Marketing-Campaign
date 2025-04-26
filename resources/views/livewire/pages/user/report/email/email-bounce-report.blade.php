<div
    class="flex flex-col p-4 md:p-6 rounded-md border border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Email Bounce Report
        </h2>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0">
            <div class="relative w-full">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search emails..."
                    class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <x-primary-select-input wire:model.live="type" class="w-full sm:w-48">
                <option value="">All Types</option>
                <option value="soft">Soft</option>
                <option value="hard">Hard</option>
            </x-primary-select-input>

            <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                <option value="email">Sort by Email</option>
                <option value="type">Sort by Type</option>
                <option value="created_at">Sort by Date</option>
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

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-xs uppercase bg-neutral-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100">
                <tr>
                    <th class="p-4">Email</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @forelse($bounces as $bounce)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">{{ $bounce->email }}</td>
                    <td class="p-4 capitalize">{{ $bounce->type ?? '-' }}</td>
                    <td class="p-4">{{ $bounce->created_at?->timezone(auth()->user()->timezone ?? $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i:s A') ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-4 text-center text-neutral-500 dark:text-neutral-400">
                        No bounces found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $bounces->links() }}
    </div>
</div>
