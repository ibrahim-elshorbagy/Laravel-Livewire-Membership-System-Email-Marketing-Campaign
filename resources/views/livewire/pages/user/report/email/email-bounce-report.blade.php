<div
    class="flex flex-col p-4 rounded-md border md:p-6 border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <div
        class="p-4 mb-4 text-yellow-800 bg-yellow-50 rounded-lg border border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-300/10 dark:text-yellow-300">
        <div class="flex gap-2 items-center">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">Important Note!</span>
        </div>
        <p class="mt-2 text-sm">Email lists will not be affected by bounces until you click "Affect Email List"
            button.</p>
    </div>
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-gray-100 md:mb-0">
            Email Bounce Report
        </h2>

        <div class="flex flex-wrap gap-2 justify-center items-start space-x-2">

            <x-primary-create-button type="button" x-on:click="$dispatch('open-modal', 'add-emails-modal');">
                Add Emails
            </x-primary-create-button>

        <!--Apply to Email List Button -->
            <x-primary-info-button type="button" wire:click="applyToEmailList">
                <span wire:loading.remove wire:target="applyToEmailList">Affect Email List</span>
                <span wire:loading wire:target="applyToEmailList" class="flex items-center">
                    <i class="fa-duotone fa-solid fa-spinner fa-spin"></i>
                    <span class="ml-2">Processing...</span>
            </x-primary-info-button>

        <!--Delete All Report Emails -->
            <x-primary-danger-button type="button" wire:confirm="Are you sure you want to delete All Report Emails?" wire:click="DeleteAllEmails">
                <span wire:loading.remove wire:target="DeleteAllEmails">Delete All Report Emails</span>
                <span wire:loading wire:target="DeleteAllEmails" class="flex items-center">
                    <i class="fa-duotone fa-solid fa-spinner fa-spin"></i>
                    <span class="ml-2">Deleting...</span>
            </x-primary-danger-button>

            <x-primary-info-link href="{{ route('user.report.email-filters') }}" wire:navigate>
                <div class="flex gap-2 justify-center items-center">
                    Emails Filters <i class="fa-solid fa-arrow-right"></i>
                </div>
            </x-primary-info-link>
        </div>
    </header>

    <livewire:pages.user.report.email.email-bounce.email-bounce-modal>
    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-2 items-start space-x-2">
            <div class="relative w-96">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search emails..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
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
    <!-- Bulk Actions -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
            @if(count($selectedBounces) > 0)
            <span class="text-sm font-medium">{{ count($selectedBounces) }} items selected</span>
            <x-primary-danger-button wire:click="bulkDelete"
                wire:confirm="Are you sure you want to delete the selected servers?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </x-primary-danger-button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-xs uppercase bg-neutral-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100">
                <tr>
                    <th class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @forelse($bounces as $bounce)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedBounces" value="{{ $bounce->id }}" class="rounded">
                    </td>

                    <td class="p-4">{{ $bounce->email }}</td>
                    <td class="p-4 capitalize">{{ $bounce->type ?? '-' }}</td>
                    <td class="p-4 text-nowrap">{{ $bounce->created_at?->timezone(auth()->user()->timezone ??
                        $globalSettings['APP_TIMEZONE'])->format('d/m/Y h:i:s A') ?? '' }}</td>
                    <td class="flex gap-3 justify-center p-4">
                        <button type="button"
                            x-on:click="$dispatch('open-modal', 'edit-email-modal'); $wire.selectedEmailId = {{ $bounce->id }}; $wire.edit_email = `{{ $bounce->email ?? '' }}`; $wire.edit_type = `{{ $bounce->type ?? '' }}`"
                            class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                            Edit
                        </button>
                        <button wire:click="deleteEmail({{ $bounce->id }})" wire:confirm="Are you sure you want to delete this Email?"
                            class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-neutral-500 dark:text-neutral-400">
                        No bounces found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Single Reusable Edit Email Modal -->
    <x-modal name="edit-email-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium">Email</h2>
            <form wire:submit.prevent="saveEmail" class="mt-4">
                <div class="space-y-4">
                    <x-input-label for="edit_email" value="Edit Email" />
                    <x-text-input wire:model="edit_email" id="edit_email" type="text"
                        class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('edit_email')" class="mt-2" />

                    <x-input-label for="edit_type" value="Edit Type" />
                    <x-primary-select-input wire:model="edit_type" id="edit_type" class="w-full">
                        <option value="hard">hard</option>
                        <option value="soft">soft</option>
                    </x-primary-select-input>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-email-modal')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        Update
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </x-modal>
    <!-- Pagination -->
    <div class="mt-4">
        {{ $bounces->links() }}
    </div>



</div>
