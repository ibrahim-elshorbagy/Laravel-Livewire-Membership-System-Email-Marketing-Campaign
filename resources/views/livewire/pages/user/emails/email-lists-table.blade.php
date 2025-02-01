<div
    class="p-6 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Filters and Actions -->
    <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <!-- Search -->
            <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search emails..." class="w-64" />

            <!-- Status Filter -->
            <x-primary-select-input wire:model.live="statusFilter" class="!w-32">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </x-primary-select-input>

            <!-- Per Page Selector -->
            <x-primary-select-input wire:model.live="perPage" class="!w-32">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </x-primary-select-input>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
            @if(count($selectedEmails) > 0)
            <x-primary-create-button wire:click="bulkUpdateStatus(true)" class="bg-green-600 hover:bg-green-700">
                Mark Active
            </x-primary-create-button>
            <x-primary-danger-button wire:click="bulkUpdateStatus(false)" class="bg-red-600 hover:bg-red-700">
                Mark Inactive
            </x-primary-danger-button>
            <x-primary-danger-button wire:click="bulkDelete"
                onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                Delete Selected ({{ count($selectedEmails) }})
            </x-primary-danger-button>
            @endif
            <x-primary-info-button href="{{ route('user.emails.create') }}" wire:navigate>
                Add New Emails
            </x-primary-info-button>
        </div>
    </div>


    <!-- Table -->
    <table class="w-full">
        <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
            <tr>
                <th class="px-4 py-2 text-left">
                    <input type="checkbox" wire:model.live="selectPage"
                        class="text-indigo-600 border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Added</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($emails as $email)
            <tr class="border-t border-gray-200 dark:border-gray-700">
                <td class="px-4 py-2">
                    <input type="checkbox" wire:model.live="selectedEmails" value="{{ $email->id }}"
                        class="text-indigo-600 border-gray-300 rounded shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </td>
                <td class="px-4 py-2">{{ $email->email }}</td>
                <td class="px-4 py-2">
                    <button wire:click="toggleStatus({{ $email->id }})"
                        class="{{ $email->active ? 'text-green-500 hover:text-green-700' : 'text-red-500 hover:text-red-700' }} cursor-pointer">
                        {{ $email->active ? 'Active' : 'Inactive' }}
                    </button>
                </td>
                <td class="px-4 py-2">{{ $email->created_at->format('M d, Y H:i') }}</td>
                <td class="px-4 py-2 text-center">
                    <x-primary-danger-button wire:click="deleteEmail({{ $email->id }})"
                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                        Delete
                    </x-primary-danger-button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $emails->links() }}
    </div>
</div>
