<div class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
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
        @if(!$emailLimit['show'] && $user->balance('Subscribers Limit') != 0)
        <x-primary-info-button href="{{ route('user.emails.create') }}" wire:navigate>
            Add New Emails
        </x-primary-info-button>
        @endif
    </div>

    <!-- Table -->
    <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">Email</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Added</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($emails as $email)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedEmails" value="{{ $email->id }}" class="rounded">
                    </td>
                    <td class="p-4">
                        <div class="flex flex-col">
                            <span class="text-neutral-900 dark:text-neutral-100">
                                {{ $email->email }}
                            </span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex overflow-hidden rounded-lg px-1 py-0.5 text-xs font-medium
                            {{ $email->active ? 'text-green-300 bg-green-300/10' : 'text-red-500 bg-red-500/10' }}">
                            {{ $email->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-4">{{ $email->created_at->format('M d, Y') }}</td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <button wire:click="toggleStatus({{ $email->id }})"
                                class="inline-flex items-center px-2 py-1 text-xs rounded-md
                                {{ $email->active ? 'text-red-500 bg-red-500/10 hover:bg-red-500/20' : 'text-green-300 bg-green-300/10 hover:bg-green-300/20' }}">
                                {{ $email->active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button wire:click="deleteEmail({{ $email->id }})"
                                onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                                class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
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
