<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <div>
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Campaign Progress: {{ $campaign->title }}
            </h2>
        </div>

        @if($historyRecords->count() > 0)
        <div class="flex gap-2">
            @if(!empty($selectedRecords))
            <x-danger-button wire:click="deleteSelected"
                wire:confirm="Are you sure you want to delete selected records?">
                Delete Selected ({{ count($selectedRecords) }})
            </x-danger-button>
            @endif

            <x-danger-button wire:click="deleteAll" wire:confirm="Are you sure you want to delete all records?">
                Delete All
            </x-danger-button>
        </div>
        @endif
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search by email..."
                    class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-48">
                    <option value="sent_time">Sort by Time</option>
                    <option value="status">Sort by Status</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="sortDirection" class="w-48">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="perPage" class="w-48">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </x-primary-select-input>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-neutral-100 dark:bg-neutral-800">
                <tr>
                    <th class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Sent Time</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse($historyRecords as $record)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedRecords" value="{{ $record->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4">{{ $record->email->email }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full {{
                            $record->status === 'sent'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400'
                                : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400'
                        }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td class="p-4">{{ $record->sent_time->format('M d, Y H:i:s') }}</td>
                    <td class="p-4">
                        <button wire:click="deleteRecord({{ $record->id }})" wire:confirm="Are you sure?"
                            class="px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">
                        No records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $historyRecords->links() }}
    </div>
</div>
