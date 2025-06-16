<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            System Emails
        </h2>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.site-settings-emails.form') }}"
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white bg-sky-600 rounded-md border border-sky-300 transition duration-150 ease-in-out cursor-pointer md:px-4 md:py-2 text-nowrap dark:bg-sky-900 group dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Create New Email Template
            </a>
        </div>
    </header>

    <!-- Email Lists Tabs -->
    <x-tabs selected-tab="{{ $selectedList }}">
        <x-slot name="tabs">
            <!-- All Emails Tab -->
            <x-tab name="all" :active="$selectedList === 'all'">
                All Emails
            </x-tab>

            <!-- List Tabs -->
            @foreach($lists as $list)
            <x-tab name="{{ $list->name }}" :active="$selectedList === $list->name">
                {{ $list->name }}
                <span class="px-2 py-1 text-xs rounded-full bg-neutral-200 dark:bg-neutral-700">
                    {{ $list->emails_count }}
                </span>
                <x-slot name="actions">
                    <div class="flex items-center ml-2 opacity-0 transition-opacity text-nowrap group-hover:opacity-100">

                        <button type="button" class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                            x-on:click="$dispatch('open-modal', 'edit-list-modal');$wire.editingListId = {{ $list->id }};$wire.listName = '{{ $list->name }}'; ">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button" wire:click="deleteList({{ $list->id }})"
                            wire:confirm="Are you sure you want to delete this list?"
                            class="ml-2 text-neutral-400 hover:text-red-600 dark:hover:text-red-500">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </x-slot>

            </x-tab>
            @endforeach

            <!-- Create List Button -->
            <div class="flex items-center">
                <button type="button" x-data @click="$dispatch('open-modal', 'create-list')"
                    class="flex items-center px-3 py-2 text-sm font-medium text-sky-600 rounded-lg dark:text-sky-400 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <i class="mr-1 fas fa-plus"></i> New List
                </button>
            </div>
        </x-slot>

        <x-slot name="content">
            <!-- Search and Filters -->
            <div class="mb-6">
                <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
                    <div class="relative flex-1">
                        <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search Templates..."
                            class="pl-10 w-full" />
                        <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-search"></i>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-40">
                            <option value="updated_at">Sort by Date</option>
                            <option value="slug">Sort by Title</option>
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

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-4">
                    @if(count($selectedTemplates) > 0)
                    <span class="text-sm font-medium">{{ count($selectedTemplates) }} items selected</span>
                    <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected templates?"
                        class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                        Delete Selected
                    </button>
                    <button wire:click="openAssignListModal"
                        class="px-3 py-1 text-sm text-white bg-blue-500 rounded-md hover:bg-blue-600">
                        Assign to List
                    </button>
                    @if($selectedList != 'all')
                    <button wire:click="removeFromList"
                        wire:confirm="Are you sure you want to remove selected templates from this list?"
                        class="px-3 py-1 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Remove from List
                    </button>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Tab Content -->
            <x-tab-panel name="all" :active="$selectedList === 'all'">
                @include('livewire.pages.admin.site-settings.system.system-emails.partials.emails-table')
            </x-tab-panel>

            @foreach($lists as $list)
            <x-tab-panel name="{{ $list->name }}" :active="$selectedList === $list->name">
                @include('livewire.pages.admin.site-settings.system.system-emails.partials.emails-table')
            </x-tab-panel>
            @endforeach
        </x-slot>
    </x-tabs>

    <!-- Create List Modal -->
    <x-modal name="create-list" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Create New Email List</h2>
            <div class="mt-4">
                <x-text-input wire:model="listName" id="list-name" class="w-full" placeholder="List name" />
                <x-input-error :messages="$errors->get('listName')" :messages="$errors->get('listName')" for="listName" class="mt-2" />
            </div>
            <div class="flex justify-end mt-6 space-x-2">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'create-list')">Cancel</x-secondary-button>
                <x-primary-create-button wire:click="createList">Create</x-primary-create-button>
            </div>
        </div>
    </x-modal>

    <!-- Edit List Modal -->
    <x-modal name="edit-list-modal" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Edit List</h2>
            <div class="mt-4">
                <x-text-input wire:model="listName" id="edit-list-name" class="w-full" placeholder="List name" />
                <x-input-error :messages="$errors->get('listName')" for="listName" class="mt-2" />
            </div>
            <div class="flex justify-end mt-6 space-x-2">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-list-modal')">Cancel</x-secondary-button>
                <x-primary-create-button wire:click="updateList">Update</x-primary-create-button>
            </div>
        </div>
    </x-modal>

    <!-- Assign List Modal -->
    <x-modal name="assign-list-modal" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Assign Templates to List</h2>
            <div class="mt-4">
                <x-primary-select-input wire:model="assignListId" class="w-full">
                    <option value="">Select a list</option>
                    @foreach($lists as $list)
                    <option value="{{ $list->id }}">{{ $list->name }}</option>
                    @endforeach
                </x-primary-select-input>
                <x-input-error :messages="$errors->get('assignListId')" for="assignListId" class="mt-2" />
            </div>
            <div class="flex justify-end mt-6 space-x-2">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'assign-list-modal')">Cancel</x-secondary-button>
                <x-primary-create-button wire:click="assignToList">Assign</x-primary-create-button>
            </div>
        </div>
    </x-modal>
</div>
