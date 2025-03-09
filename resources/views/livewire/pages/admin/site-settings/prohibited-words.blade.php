<div class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Prohibited Words Management
        </h2>
        <div class="mt-4 md:mt-0">
            <button type="button" x-on:click="$dispatch('open-modal', 'add-words-modal')"
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white bg-sky-600 rounded-md border border-sky-300 transition duration-150 ease-in-out cursor-pointer md:px-4 md:py-2 text-nowrap dark:bg-sky-900 group dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Add New Words
            </button>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search prohibited words"
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="word">Sort by Word</option>
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
    </div>

    <!-- Bulk Actions -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
            @if(count($selectedWords) > 0)
            <span class="text-sm font-medium">{{ count($selectedWords) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected words?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">Word</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($words as $word)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedWords" value="{{ $word->id }}" class="rounded">
                    </td>
                    <td class="p-4">{{ $word->word }}</td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <button type="button" wire:click="startEditing({{ $word->id }})" x-on:click="$dispatch('open-modal', 'edit-word-modal')"
                                class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                Edit
                            </button>

                            <button wire:click="deleteWord({{ $word->id }})"
                                wire:confirm="Are you sure you want to delete this word?"
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
        {{ $words->links() }}
    </div>

    <!-- Add Words Modal -->
    <x-modal name="add-words-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                Add Prohibited Words
            </h2>

            <div class="mt-4">
                <x-textarea-input wire:model="newWords" class="w-full" placeholder="Enter words separated by commas" />
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    Enter multiple words separated by commas<br> (badword,secondbadword).
                </p>
            </div>

            <div class="flex justify-end mt-6 space-x-2">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'add-words-modal')">
                    Cancel
                </x-secondary-button>
                <x-primary-create-button wire:click="addWords" x-on:click="$dispatch('close-modal', 'add-words-modal')">
                    Add Words
                </x-primary-create-button>
            </div>
        </div>
    </x-modal>

    <!-- Edit Word Modal -->
    <x-modal name="edit-word-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                Edit Prohibited Word
            </h2>

            <div class="mt-4">
                <x-text-input wire:model="editedWord" class="w-full" placeholder="Enter word" />
            </div>

            <div class="flex justify-end mt-6 space-x-2">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-word-modal')">
                    Cancel
                </x-secondary-button>
                <x-primary-create-button wire:click="updateWord" x-on:click="$dispatch('close-modal', 'edit-word-modal')">
                    Update
                </x-primary-create-button>
            </div>
        </div>
    </x-modal>
</div>
