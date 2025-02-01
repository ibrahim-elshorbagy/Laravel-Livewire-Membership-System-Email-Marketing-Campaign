<div class="container p-4 mx-auto">
    <!-- Warning Alert -->
    @if($emailLimit['show'])
    <div class="p-4 mb-6 text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
        role="alert">
        <div class="flex flex-wrap items-center gap-2">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <h3 class="text-lg font-medium">Warning: Email Limit Exceeded</h3>
        </div>
        <div class="mt-2 text-sm">
            You currently have {{ $emailLimit['excess'] }} emails more than your plan allows. Your plan limit is {{
            $emailLimit['allowed'] }} emails.
        </div>
    </div>
    @endif

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <!-- Search Box -->
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search..." class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="statusFilter" class="w-full sm:w-32">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="perPage" class="w-full sm:w-32">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </x-primary-select-input>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-2 mb-6">
        @if(count($selectedEmails) > 0)
        <x-primary-create-button wire:click="bulkUpdateStatus(true)" class="bg-green-600 hover:bg-green-700">
            Mark Active
        </x-primary-create-button>
        <x-primary-danger-button wire:click="bulkUpdateStatus(false)">
            Mark Inactive
        </x-primary-danger-button>
        <x-primary-danger-button wire:click="bulkDelete"
            onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
            Delete Selected ({{ count($selectedEmails) }})
        </x-primary-danger-button>
        @endif
        @if(!$emailLimit['show'])
        <x-primary-info-button href="{{ route('user.emails.create') }}" wire:navigate>
            Add New Emails
        </x-primary-info-button>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-neutral-800">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-700">
                <tr>
                    <th class="p-4 text-left">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th class="p-4 text-xs font-medium text-left uppercase text-neutral-500 dark:text-neutral-400">Email
                    </th>
                    <th class="p-4 text-xs font-medium text-left uppercase text-neutral-500 dark:text-neutral-400">
                        Status</th>
                    <th class="p-4 text-xs font-medium text-left uppercase text-neutral-500 dark:text-neutral-400">Added
                    </th>
                    <th class="p-4 text-xs font-medium text-left uppercase text-neutral-500 dark:text-neutral-400">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($emails as $email)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedEmails" value="{{ $email->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4">{{ $email->email }}</td>
                    <td class="p-4">
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $email->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $email->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-4">{{ $email->created_at->format('M d, Y') }}</td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <button wire:click="toggleStatus({{ $email->id }})"
                                class="px-3 py-1 text-sm rounded-md {{ $email->active ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $email->active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button wire:click="deleteEmail({{ $email->id }})"
                                onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                                class="px-3 py-1 text-sm text-white bg-red-600 rounded-md hover:bg-red-700">
                                Delete
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
        {{ $emails->links() }}
    </div>
</div>
