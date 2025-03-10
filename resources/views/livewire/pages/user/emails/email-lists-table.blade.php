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
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search..." class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <!-- Search Box -->
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="searchNameTerm" placeholder="Search..."
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
                        <x-primary-danger-button x-data="{
                            isDisabled: {{ !$selectedList || ($selectedList && $this->lists->firstWhere('id', $selectedList)?->emails_count == 0) ? 'true' : 'false' }}
                        }" wire:click="deleteEmails('{{ !empty($selectedEmails) ? 'selected' : 'all' }}')"
                            wire:confirm="{{ !empty($selectedEmails)
                            ? 'Are you sure you want to delete ' . count($selectedEmails) . ' selected emails?'
                            : 'WARNING: This will delete ALL emails in the current list. This action cannot be undone. Are you sure?' }}"
                            x-bind:class="isDisabled ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full text-sm sm:text-base" x-bind:disabled="isDisabled"
                            @mouseenter="$refs.noteBox.classList.remove('opacity-0')"
                            @mouseleave="$refs.noteBox.classList.add('opacity-0')">

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
                                    {{ $this->lists->firstWhere('id', $selectedList)?->emails_count ?? 0 }}
                                </span>
                                @endif
                                @endif

                            </div>
                        </x-primary-danger-button>

                        <!-- Tooltip -->
                        <div x-ref="noteBox"
                            class="absolute left-0 z-50 p-2 mt-2 w-60 text-xs bg-white rounded-lg border shadow-lg opacity-0 transition-opacity duration-200 ease-in-out transform sm:w-72 sm:p-3 sm:text-sm dark:bg-neutral-800 dark:border-neutral-700">
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
                            @elseif($selectedList && $this->lists->firstWhere('id', $selectedList)?->emails_count != 0)
                            <div class="flex gap-1.5 items-start text-red-600 sm:gap-2 dark:text-red-500">
                                <i class="mt-0.5 text-xs sm:text-sm fas fa-exclamation-triangle"></i>
                                <div>
                                    <p class="font-medium">Warning</p>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                        Delete all {{ $this->lists->firstWhere('id', $selectedList)?->emails_count ?? 0
                                        }} emails
                                        from "{{ $this->lists->firstWhere('id', $selectedList)?->name }}"
                                    </p>
                                </div>
                            </div>
                            @elseif($selectedList && $this->lists->firstWhere('id', $selectedList)?->emails_count == 0)
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

                    <!-- Other Buttons -->
                    @if(!$emailLimit['show'] && $user->balance('Subscribers Limit') != 0 && !$this->lists->isEmpty())
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

        <!-- Tabs -->
        <div x-data="{
                                selectedTab: @entangle('selectedList').live,
                                scrollContainer: null,
                                isScrollable: false,
                                hasScrolledToEnd: false,
                                hasScrolledToStart: true,

                                init() {
                                    this.scrollContainer = this.$refs.tabsContainer;
                                    this.checkScroll();
                                    window.addEventListener('resize', () => this.checkScroll());
                                    this.scrollContainer.addEventListener('scroll', () => this.checkScroll());

                                    $wire.on('tabSelected', (listId) => {
                                        this.selectedTab = listId;
                                    });
                                },


                                checkScroll() {
                                    if (!this.scrollContainer) return;
                                    this.isScrollable = this.scrollContainer.scrollWidth > this.scrollContainer.clientWidth;
                                    this.hasScrolledToStart = this.scrollContainer.scrollLeft <= 0;
                                    this.hasScrolledToEnd = this.scrollContainer.scrollLeft + this.scrollContainer.clientWidth >= this.scrollContainer.scrollWidth;
                                },

                                scrollLeft() {
                                    this.scrollContainer.scrollBy({ left: -200, behavior: 'smooth' });
                                },

                                scrollRight() {
                                    this.scrollContainer.scrollBy({ left: 200, behavior: 'smooth' });
                                }
                            }">
            <div class="flex relative items-center">
                <!-- Left Scroll Button -->
                <button x-show="isScrollable && !hasScrolledToStart" x-on:click="scrollLeft"
                    class="absolute left-0 z-10 p-2 rounded-full shadow-md transition-all text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    style="transform: translateX(-50%);">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <!-- Tabs Container -->
                <div x-ref="tabsContainer"
                    class="flex overflow-x-auto gap-2 py-4 border-b text-md scrollbar-hide border-neutral-300 dark:border-neutral-700 scroll-smooth"
                    style="scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
                    @foreach($this->lists as $list)
                    <div class="flex items-center px-4 py-2 rounded-lg transition-all text-md group text-nowrap hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        :class="{
                                            'bg-neutral-100 dark:bg-neutral-800 border-b-2 border-neutral-600 dark:border-orange-500': selectedTab == {{ $list->id }},
                                            'bg-neutral-50 dark:bg-neutral-900': selectedTab != {{ $list->id }}
                                        }">
                        <button type="button" x-on:click="selectedTab = {{ $list->id }}"
                            wire:click.debounce.100ms="selectList({{ $list->id }})"
                            class="mr-2 font-medium text-neutral-600 dark:text-neutral-300">
                            {{ $list->name }}
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">({{ $list->emails_count
                                }})</span>
                        </button>
                        <div class="flex items-center opacity-0 transition-opacity text-nowrap group-hover:opacity-100">
                            <button type="button"
                                x-on:click="$wire.set('listName', '{{ $list->name }}'); $dispatch('open-modal', 'edit-list-{{ $list->id }}')"
                                class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
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
                    </div>
                    @endforeach
                </div>

                <!-- Right Scroll Button -->
                <button x-show="isScrollable && !hasScrolledToEnd" x-on:click="scrollRight"
                    class="absolute right-0 z-10 p-2 rounded-full shadow-md transition-all text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    style="transform: translateX(50%);">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>


            <!-- Show emails table only when a list is selected -->

            @if($selectedList)
            <div wire:loading.remove wire:target="selectList">
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
                                                <button  type="button" x-on:click="isExpanded = !isExpanded"
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
                                    <div x-show="isExpanded" x-collapse class="mt-2">
                                        @if($email->history->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($email->history->sortByDesc('sent_time') as $record)
                                            <div class="relative group p-3 text-sm rounded-lg {{ $record->status === 'sent'
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
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                                        {{ $record->status === 'sent'
                                                                            ? 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                                            : 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-300'
                                                                        }}">
                                                            {{ ucfirst($record->status) }}
                                                        </span>
                                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
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

                                        <button wire:click="deleteEmails({{ $email->id }})"
                                            wire:confirm="Are you sure you want to delete this email?"
                                            class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    @if($selectedList && $emails->hasPages())
                    <div class="mt-4">
                        {{ $emails->links() }}
                    </div>
                    @endif
                </div>
                @else
                <div class="p-4 text-center text-gray-500">
                    @if($search)
                    No emails match your search " {{ $search }} " in this list.
                    @else
                    No emails found in this list.
                    @endif
                    @if(!$hasActiveJobsFlag && !$emailLimit['show'] && $user->balance('Subscribers Limit') != 0)
                    <div class="mt-2">
                        <x-primary-info-button href="{{ route('user.emails.create', ['list_id' => $selectedList]) }}"
                            wire:navigate>
                            Add New Emails
                        </x-primary-info-button>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <div wire:loading.class.remove="hidden" wire:loading.class='flex' wire:target="selectList"
                class="hidden justify-center items-center p-4">
                <div class="w-8 h-8 rounded-full border-4 border-blue-500 animate-spin border-t-transparent"></div>
            </div>
            @else
            <div class="p-8 text-center">
                @if($this->lists->isEmpty())
                <!-- No lists exist -->
                <div class="flex flex-col gap-4 items-center">
                    <div class="p-4 text-neutral-600 dark:text-neutral-400">
                        <i class="mb-2 text-4xl fas fa-list-ul"></i>
                        <h3 class="mt-2 text-lg font-medium">No Mailing Lists Found</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-500">
                            Create your first mailing list to start managing your emails
                        </p>
                    </div>
                    <x-primary-create-button x-on:click="$dispatch('open-modal', 'create-list')" class="mt-2">
                        <i class="mr-2 fas fa-plus"></i> Create Your First List
                    </x-primary-create-button>
                </div>
                @else
                <!-- Lists exist but none selected -->
                <div class="flex flex-col gap-4 items-center">
                    <div class="p-4 text-neutral-600 dark:text-neutral-400">
                        <i class="mb-2 text-4xl fas fa-hand-point-up"></i>
                        <h3 class="mt-2 text-lg font-medium">Select a List</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-500">
                            Please select a list from above to view and manage your emails
                        </p>
                    </div>
                </div>
                @endif
            </div>
            @endif




        </div>

        <!-- Modals -->
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
                        <x-primary-button type="submit">
                            Create
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>

        <!-- Edit Modals -->
        @foreach($this->lists as $list)
        <x-modal name="edit-list-{{ $list->id }}" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium">Edit List</h2>
                <form wire:submit="updateList({{ $list->id }})" class="mt-4">
                    <div>
                        <x-input-label for="listName" value="List Name" />
                        <x-text-input wire:model="listName" id="listName" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('listName')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-list-{{ $list->id }}')">
                            Cancel
                        </x-secondary-button>
                        <x-primary-button type="submit">
                            Update
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
        @endforeach
    </div>
</div>
