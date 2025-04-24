<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">



    <!-- Header -->
    <div class="mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                Mailing list
            </h2>
        </div>
    </div>


    <!-- Warning: Email Limit Exceeded -->
    @if($emailLimit['show'])
    <div class="p-4 mb-6 text-red-800 bg-red-50 rounded-lg border border-red-300 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
        role="alert">
        <div class="flex flex-wrap gap-2 items-center">
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
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Email Search..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <!-- Search Box -->
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="searchName" placeholder="Name Search..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>
            <!-- Filters -->
            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="orderBy" class="w-full sm:w-32">
                    <option value="email">Email</option>
                    <option value="name">Name</option>
                    <option value="soft_bounce_counter">Soft Bounce</option>
                    <option value="is_hard_bounce">Hard Bounce</option>
                </x-primary-select-input>
                <x-primary-select-input wire:model.live="sortDirection" class="w-full sm:w-32">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
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





    <!-- Total Emails -->
    <div class="p-3 my-4 bg-blue-100 rounded-lg dark:bg-blue-900">
        <ul class="pl-5 text-sm list-disc text-gray-700 dark:text-gray-200">
            <li>
                <i class="mr-2 text-blue-600 fas fa-envelope dark:text-blue-300"></i>
                Total Emails: <span class="font-bold">{{ $this->lists->sum('emails_count') }}</span>
            </li>
        </ul>
    </div>


    <!-- job progress  -->


    <livewire:pages.user.emails.partials.job-progress-component />



    <!-- Start The list  -->
    <div class="flex flex-col p-3 rounded-md border md:p-6">
        <!-- List Management -->
        <div class="mb-6 md:flex md:items-center md:justify-between">

            <!-- Header -->
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold sm:text-3xl sm:truncate">
                    Mailing Lists
                </h2>
            </div>



            <!-- Action Buttons -->
            <div>
                @if(!$hasActiveJobsFlag)
                <div class="flex flex-col gap-2 mt-2 sm:flex-row">


                    <!-- Delete Button  -->
                    <div class="relative">
                        <x-primary-danger-button x-data="{}"
                            :disabled="!$selectedList || ($this->lists->firstWhere('name', $selectedList)?->emails_count == 0)"
                            x-bind:class="$el.disabled ? 'opacity-50 cursor-not-allowed' : ''"
                            wire:click="deleteEmails('{{ !empty($selectedEmails) ? 'selected' : 'all' }}')"
                            wire:confirm="{{ !empty($selectedEmails)
                            ? 'Are you sure you want to delete ' . count($selectedEmails) . ' selected emails?'
                            : 'WARNING: This will delete ALL emails in the current list. This action cannot be undone. Are you sure?' }}"
                            class="w-full text-sm sm:text-base" @mouseenter="$refs.noteBox.classList.remove('hidden')"
                            @mouseleave="$refs.noteBox.classList.add('hidden')">

                            <div class="flex gap-1 items-center sm:gap-2">

                                <i class="text-xs sm:text-sm fas fa-trash"></i>
                                @if(!empty($selectedEmails))
                                <span class="text-xs sm:text-sm">Delete Selected</span>

                                <span class="px-1.5 py-0.5 text-xs bg-red-700 rounded-full sm:px-2">
                                    {{ count($selectedEmails) }}
                                </span>
                                @else
                                <span class="text-xs sm:text-sm">Delete All</span>

                                @if($selectedList)
                                <span class="px-1.5 py-0.5 text-xs bg-red-700 rounded-full sm:px-2">
                                    {{ $this->lists->firstWhere('name', $selectedList)?->emails_count ?? 0 }}
                                </span>
                                @endif
                                @endif

                            </div>
                        </x-primary-danger-button>

                        <!-- Tooltip -->
                        <div x-ref="noteBox"
                            class="hidden absolute left-0 z-50 p-2 mt-2 w-60 text-xs bg-white rounded-lg border shadow-lg transition-opacity duration-200 ease-in-out transform sm:w-72 sm:p-3 sm:text-sm dark:bg-neutral-800 dark:border-neutral-700">
                            @if(!empty($selectedEmails))
                            <div class="flex gap-1.5 items-start text-yellow-600 sm:gap-2 dark:text-yellow-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-info-circle"></i>
                                <div>
                                    <p class="font-medium">Selected Emails Deletion</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Delete {{ count($selectedEmails) }} selected emails
                                    </p>
                                </div>
                            </div>
                            @elseif($selectedList && $this->lists->firstWhere('name', $selectedList)?->emails_count !=
                            0)
                            <div class="flex gap-1.5 items-start text-red-600 sm:gap-2 dark:text-red-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-exclamation-triangle"></i>
                                <div>
                                    <p class="font-medium">Warning</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Delete all {{ $this->lists->firstWhere('name', $selectedList)?->emails_count ??
                                        0
                                        }} emails
                                        from "{{ $this->lists->firstWhere('name', $selectedList)?->name }}"
                                    </p>
                                </div>
                            </div>
                            @elseif($selectedList && $this->lists->firstWhere('name', $selectedList)?->emails_count ==
                            0)
                            <div class="flex gap-1.5 items-start text-yellow-600 sm:gap-2 dark:text-yellow-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-exclamation-circle"></i>
                                <div>
                                    <p class="font-medium">Empty List</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        No emails to delete
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="flex gap-1.5 items-start text-blue-600 sm:gap-2 dark:text-blue-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-info-circle"></i>
                                <div>
                                    <p class="font-medium">Select List</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Please select a list first
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Hard Bounce Delete Button  -->
                    <div class="relative">
                        <x-primary-danger-button x-data="{}"
                            :disabled="!$selectedList || $this->getHardBounceCount() == 0"
                            x-bind:class="$el.disabled ? 'opacity-50 cursor-not-allowed' : ''"
                            wire:click="deleteEmails('hard_bounce')"
                            wire:confirm="WARNING: This will delete ALL hard bounce emails in the current list. This action cannot be undone. Are you sure?"
                            class="w-full text-sm sm:text-base"
                            @mouseenter="$refs.hardBounceNoteBox.classList.remove('hidden')"
                            @mouseleave="$refs.hardBounceNoteBox.classList.add('hidden')">

                            <div class="flex gap-1 items-center sm:gap-2">
                                <i class="text-xs sm:text-sm fas fa-trash"></i>
                                <span class="text-xs sm:text-sm">Delete Hard Bounces</span>

                                @if($selectedList)
                                <span class="px-1.5 py-0.5 text-xs bg-red-700 rounded-full sm:px-2">
                                    {{ $this->getHardBounceCount() }}
                                </span>
                                @endif
                            </div>
                        </x-primary-danger-button>


                        <!-- Tooltip -->
                        <div x-ref="hardBounceNoteBox"
                            class="hidden absolute left-0 z-50 p-2 mt-2 w-60 text-xs bg-white rounded-lg border shadow-lg transition-opacity duration-200 ease-in-out transform sm:w-72 sm:p-3 sm:text-sm dark:bg-neutral-800 dark:border-neutral-700">
                            @if(!$selectedList)
                            <div class="flex gap-1.5 items-start text-blue-600 sm:gap-2 dark:text-blue-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-info-circle"></i>
                                <div>
                                    <p class="font-medium">Select List</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Please select a list first
                                    </p>
                                </div>
                            </div>
                            @elseif($this->getHardBounceCount() == 0)
                            <div class="flex gap-1.5 items-start text-yellow-600 sm:gap-2 dark:text-yellow-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-exclamation-circle"></i>
                                <div>
                                    <p class="font-medium">No Hard Bounces</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        No hard bounce emails to delete
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="flex gap-1.5 items-start text-red-600 sm:gap-2 dark:text-red-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-exclamation-triangle"></i>
                                <div>
                                    <p class="font-medium">Warning</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Delete all {{ $this->getHardBounceCount() }} hard bounce emails
                                        from "{{ $this->lists->firstWhere('name', $selectedList)?->name }}"
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Other Buttons -->
                    @if(!$emailLimit['show'] && $subscriberBalance != 0 && !$this->lists->isEmpty())
                    <x-primary-info-button class="text-xs sm:text-sm"
                        href="{{ $selectedList ? route('user.emails.create', ['list_id' => $selectedList]) : route('user.emails.create') }}"
                        wire:navigate>
                        <div class="flex gap-2 items-center"><i class="mr-1 fas fa-plus"></i> Add Emails</div>

                    </x-primary-info-button>
                    @endif

                    <x-primary-create-button class="text-xs sm:text-sm"
                        x-on:click="$dispatch('open-modal', 'create-list')">

                        <div class="flex gap-2 items-center"><i class="mr-1 fas fa-list"></i> New List</div>
                    </x-primary-create-button>
                </div>
                @else
                <div
                    class="p-2 w-full text-xs text-yellow-800 bg-yellow-100 rounded-lg sm:text-sm dark:bg-yellow-900 dark:text-yellow-300">
                    <p class="font-medium">Actions Disabled</p>
                    <p class="mt-0.5">Please wait for current jobs to complete</p>
                </div>
                @endif
            </div>


        </div>

        <x-tabs :selected-tab="$selectedList" default-tab="default-tab-name">
            <x-slot name="tabs">
                @foreach($this->lists as $list)
                <x-tab name="{{ $list->name }}">
                    {{ $list->name }}
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">({{ $list->emails_count }})</span>
                    <x-slot name="actions">
                        <div
                            class="flex items-center ml-2 opacity-0 transition-opacity text-nowrap group-hover:opacity-100">

                            <button type="button"
                                class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                x-on:click="$dispatch('open-modal', 'edit-list-modal');$wire.editingListId = {{ $list->id }};$wire.listName = '{{ $list->name }}'; ">
                                <i class="fas fa-edit"></i>
                            </button>

                            @if(!$hasActiveJobsFlag)
                            <button type="button" wire:click="deleteList({{ $list->id }})"
                                wire:confirm="Are you sure you want to delete this list?"
                                class="ml-2 text-neutral-400 hover:text-red-600 dark:hover:text-red-500">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </x-slot>
                </x-tab>
                @endforeach
            </x-slot>
            <x-slot name="content">
                @foreach($this->lists as $list)
                <x-tab-panel name="{{ $list->name }}">
                    <!-- Only render content for the active tab -->
                    @if($list->name === $selectedList)
                    @if($emails->count() > 0)
                    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
                        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
                            <thead
                                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                                <tr>
                                    <!-- Checkbox column -->
                                    <th scope="col" class="p-4 w-10">
                                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                                    </th>

                                    <!-- Combined Email/Name column -->
                                    <th scope="col" class="p-4">
                                        <div class="flex justify-between items-center">
                                            <span class="w-[350px]">Email</span>
                                            <span class="w-[350px]">Name</span>
                                            <span class="w-[350px]">Soft Bounce</span>
                                            <span class="w-[350px]">Hard Bounce</span>
                                            <span class="w-[130px]"></span> {{-- Spacer For History --}}
                                        </div>
                                    </th>

                                    <!-- Actions column -->
                                    <th scope="col" class="p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                                @foreach($emails as $email)
                                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                    wire:key="email-row-{{ $email->id }}">
                                    <td class="p-4">
                                        <input type="checkbox" wire:model.live="selectedEmails" value="{{ $email->id }}"
                                            class="rounded">
                                    </td>


                                    <td x-data="{ isExpanded: false }" class="p-4 w-full">
                                        <div class="flex justify-between items-center">

                                            <span class="w-[350px]">{{ $email->email }}</span>
                                            <span class="w-[350px]">{{ $email->name }}</span>
                                            <span class="w-[350px]">
                                                @if($email->soft_bounce_counter > 0)
                                                <span
                                                    class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                                                    {{ $email->soft_bounce_counter }}
                                                </span>
                                                @endif
                                            </span>
                                            <span class="w-[350px]">
                                                @if($email->is_hard_bounce)
                                                <span
                                                    class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                                                    Hard Bounce
                                                </span>
                                                @endif
                                            </span>

                                            <div class="flex self-end gap-2 items-center w-[130px] justify-end">
                                                @if($email->history->count() > 0)
                                                <!-- Delete All History Button -->
                                                <button wire:click="deleteAllHistory({{ $email->id }})"
                                                    wire:confirm="Are you sure you want to delete all history records?"
                                                    class="px-2 py-1 text-xs text-red-500 rounded-md transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                @endif

                                                <!-- View History Button - Always visible -->
                                                <button type="button" x-on:click="isExpanded = !isExpanded"
                                                    class="flex gap-2 items-center self-end px-2 py-1 text-xs rounded-md transition-colors text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                                                    @if($email->history->count() > 0)
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-medium text-purple-700 bg-purple-100 rounded-full dark:bg-purple-900/50 dark:text-purple-400">
                                                        {{ $email->history->count() }}
                                                    </span>
                                                    History
                                                    @else
                                                    <span class="flex gap-1 items-center">
                                                        <i class="text-xs text-red-500 fas fa-info-circle"></i>
                                                        No History
                                                    </span>
                                                    @endif
                                                    <i class="transition-transform duration-200 fas fa-chevron-down"
                                                        :class="{'rotate-180': isExpanded}"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- History Records -->
                                        <div x-cloak x-show="isExpanded" x-collapse class="mt-2">
                                            @if($email->history->count() > 0)
                                            <div class="space-y-2">
                                                @foreach($email->history->sortByDesc('sent_time') as $record)
                                                <div
                                                    class="relative group p-3 text-sm rounded-lg {{ $record->status === 'sent'
                                                                                                            ? 'bg-green-100 dark:bg-green-900/30'
                                                                                                            : 'bg-red-100 dark:bg-red-900/30' }}">

                                                    <!-- Delete Single History Button -->
                                                    <button wire:click="deleteHistory({{ $record->id }})"
                                                        wire:confirm="Are you sure you want to delete this record?"
                                                        class="absolute top-2 right-2 p-1.5 px-2 py-1 text-xs text-red-500 rounded-md opacity-0 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20 group-hover:opacity-100">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>

                                                    <div class="flex justify-between items-center">
                                                        <div class="flex items-center space-x-2">
                                                            @if($record->status === 'sent')
                                                            <i
                                                                class="text-green-600 dark:text-green-400 fas fa-check-circle"></i>
                                                            @else
                                                            <i
                                                                class="text-red-600 dark:text-red-400 fas fa-exclamation-circle"></i>
                                                            @endif
                                                            <span class="font-medium">Campaign: {{
                                                                $record->campaign?->message?->email_subject }}</span>
                                                        </div>
                                                        <div class="flex gap-2 items-center mr-10">
                                                            <span
                                                                class="px-2 py-0.5 text-xs font-medium rounded-full
                                                                                                                        {{ $record->status === 'sent'
                                                                                                                            ? 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                                                                                            : 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-300'
                                                                                                                        }}">
                                                                {{ ucfirst($record->status) }}
                                                            </span>
                                                            <span
                                                                class="text-xs text-neutral-500 dark:text-neutral-400">
                                                                {{ $record->sent_time->timezone(auth()->user()->timezone
                                                                ?? $globalSettings['APP_TIMEZONE'])->format('d M, Y
                                                                h:i:s A') }} -
                                                                {{ $record->sent_time->timezone(auth()->user()->timezone
                                                                ?? $globalSettings['APP_TIMEZONE'])->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div
                                                class="p-4 text-sm text-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                                                <i
                                                    class="mb-2 text-2xl text-neutral-400 dark:text-neutral-600 fas fa-inbox"></i>
                                                <p class="text-neutral-600 dark:text-neutral-400">No sending history
                                                    available</p>
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <div class="flex space-x-2">

                                            <button wire:click="deleteEmails('single',{{ $email->id }})"
                                                wire:confirm="Are you sure you want to delete this email?"
                                                class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                                                Delete
                                            </button>
                                            <button type="button"
                                                class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                                x-on:click="$dispatch('open-modal', 'edit-email-modal'); $wire.selectedEmailId = {{ $email->id }}; $wire.editEmail = '{{ $email->email }}'; $wire.editName = '{{ $email->name }}'; $wire.editSoftBounceCounter = '{{ $email->soft_bounce_counter }}'; $wire.editIsHardBounce = '{{ $email->is_hard_bounce }}'">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        @if($emails->hasPages())
                        <div class="mt-4" wire:ignore>
                            {{ $emails->links(data: ['scrollTo' => false]) }}
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="p-4 text-center text-gray-500">
                        @if($search || $searchName)
                        No emails match your search
                        @if($search)
                        "{{ $search }} "
                        @endif
                        @if($searchName)
                        "{{ $searchName }}"
                        @endif
                        in this list.
                        @else
                        No emails found in this list.
                        @endif
                        @if(!$hasActiveJobsFlag && !$emailLimit['show'] && $subscriberBalance != 0)
                        <div class="mt-2">
                            <x-primary-info-button
                                href="{{ route('user.emails.create', ['list_id' => $selectedList]) }}" wire:navigate>
                                Add New Emails
                            </x-primary-info-button>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endif
                </x-tab-panel>
                @endforeach
            </x-slot>
        </x-tabs>







        <!-- Modals -->

        <!-- Create New List Modal -->
        <x-modal name="create-list" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium">Create New List</h2>
                <form wire:submit="createList" class="mt-4">
                    <div>
                        <x-input-label for="listName" value="List Name" />
                        <x-text-input wire:model="listName" id="listName" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('listName')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'create-list')">
                            Cancel
                        </x-secondary-button>
                        <x-primary-create-button type="submit">
                            Create
                        </x-primary-create-button>
                    </div>
                </form>
            </div>
        </x-modal>


        <!-- Edit list Modal -->
        <x-modal name="edit-list-modal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium">Edit List</h2>
                <form wire:submit="updateList" class="mt-4">
                    <div>
                        <x-input-label for="listName" value="List Name" />
                        <x-text-input wire:model="listName" id="listName" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('listName')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-list-modal')">
                            Cancel
                        </x-secondary-button>
                        <x-primary-create-button type="submit">
                            Update
                        </x-primary-create-button>
                    </div>
                </form>
            </div>
        </x-modal>


        <!-- Edit Email Modal -->
        <x-modal name="edit-email-modal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium">Edit Email</h2>
                <form wire:submit.prevent="updateEmail" class="mt-4">
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="emailAddress" value="Email Address" />
                            <x-text-input wire:model="editEmail" id="emailAddress" type="email"
                                class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('editEmail')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="emailName" value="Name" />
                            <x-text-input wire:model="editName" id="emailName" type="text" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('editName')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="softBounceCounter" value="Soft Bounce Counter" />
                            <x-text-input wire:model="editSoftBounceCounter" id="softBounceCounter" type="number"
                                class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('editSoftBounceCounter')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="isHardBounce" value="Hard Bounce Status" />
                            <x-primary-select-input wire:model="editIsHardBounce" id="isHardBounce"
                                class="block mt-1 w-full">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </x-primary-select-input>
                            <x-input-error :messages="$errors->get('editIsHardBounce')" class="mt-2" />
                        </div>
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


    </div>
</div>
