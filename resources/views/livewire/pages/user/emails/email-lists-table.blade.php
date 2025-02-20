<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Warning Alert -->
    <div class="mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                Mailing list
            </h2>
        </div>
    </div>

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
                    <option value="FAIL">Failed</option>
                    <option value="SENT">Sent</option>
                    <option value="NULL">Empty</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-40">
                    <option value="email">Sort by Email</option>
                    <option value="status">Sort by Status</option>
                    <option value="send_time">Sort by Send Time</option>
                    <option value="sender_email">Sort by Sender</option>
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



    <!-- Action Buttons -->
    <div class="flex flex-col gap-4 mb-6">


        <div class="flex flex-wrap gap-2">
            @if(!$hasActiveJobsFlag)
            <!-- Per Page Actions -->
            @if(count($selectedEmails) > 0)
            <div class="w-full mb-2">
                <span class="font-medium text-gray-700 text-md dark:text-gray-300">
                    Current Page Actions:
                </span>
            </div>
            <x-primary-button wire:click="clearStatus('FAIL')"
                class="bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-700 dark:hover:bg-yellow-600"
                wire:confirm="Are you sure you want to clear failed status for selected emails?">
                Clear Failed Status ({{ count($selectedEmails) }})
            </x-primary-button>

            <x-primary-button wire:click="clearStatus('SENT')"
                class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600"
                wire:confirm="Are you sure you want to clear sent status for selected emails?">
                Clear Sent Status ({{ count($selectedEmails) }})
            </x-primary-button>

            <x-primary-button wire:click="clearAllStatus"
                class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600"
                wire:confirm="Are you sure you want to clear all status for selected emails?">
                Clear All Status ({{ count($selectedEmails) }})
            </x-primary-button>

            <x-primary-danger-button wire:click="bulkDelete"
                wire:confirm="Are you sure you want to delete these selected emails?">
                Delete Selected ({{ count($selectedEmails) }})
            </x-primary-danger-button>
            @endif

            <!-- Global Actions -->
            <div class="w-full">
                <span class="font-medium text-gray-700 text-md dark:text-gray-300">
                    Global Actions:
                </span>
            </div>

            <x-primary-button wire:click="clearAllFailedStatus"
                class="bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-700 dark:hover:bg-yellow-600"
                wire:confirm="Are you sure you want to clear ALL failed status emails?">
                Clear All Failed Status
            </x-primary-button>

            <x-primary-button wire:click="clearAllSentStatus"
                class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600"
                wire:confirm="Are you sure you want to clear ALL sent status emails?">
                Clear All Sent Status
            </x-primary-button>

            <x-primary-button wire:click="clearAllEmailsStatus"
                class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600"
                wire:confirm="Are you sure you want to clear ALL email statuses?">
                Clear All Statuses
            </x-primary-button>

            <x-primary-danger-button wire:click="deleteAllEmails"
                wire:confirm="WARNING: This will delete ALL your emails. This action cannot be undone. Are you sure?"
                class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600">
                Delete All Emails
            </x-primary-danger-button>

            <div class="w-full p-4 text-yellow-700 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-md dark:text-neutral-300">
                - Any Action Will Affect The Selected List Only
            </div>
            @else
            <div class="w-full p-4 text-yellow-800 bg-yellow-100 rounded-lg dark:bg-yellow-900 dark:text-yellow-300">
                <p class="font-medium">Actions Disabled</p>
                <p class="text-sm">Please wait for current jobs to complete before starting new ones.</p>
            </div>
            @endif
        </div>


    </div>

    <div class="p-3 my-4 bg-blue-100 rounded-lg dark:bg-blue-900">
        <ul class="pl-5 text-sm text-gray-700 list-disc dark:text-gray-200">
            <li>
                <i class="mr-2 text-blue-600 fas fa-envelope dark:text-blue-300"></i>
                Total Emails: <span class="font-bold">{{ $emailsCount['total'] }}</span>
                @if($selectedList)
                ({{ $emailsCount['current_list'] }} in current list)
                @endif
            </li>
        </ul>
    </div>

    <livewire:pages.user.emails.partials.job-progress-component />

    <div class="flex flex-col p-3 border rounded-md md:p-6">
        <!-- List Management -->
        <div class="mb-6 md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                    Mailing Lists
                </h2>
            </div>
            <div class="flex gap-2 mt-4 md:mt-0 md:ml-4">
                    @if(!$hasActiveJobsFlag)
                        @if(!$emailLimit['show'] && $user->balance('Subscribers Limit') != 0)
                        <x-primary-info-button href="{{ route('user.emails.create') }}" wire:navigate>
                            Add New Emails
                        </x-primary-info-button>
                        @endif
                    @endif
                <x-primary-create-button x-on:click="$dispatch('open-modal', 'create-list')">
                    Create New List
                </x-primary-create-button>
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
                            <div class="relative flex items-center">
                                <!-- Left Scroll Button -->
                                <button x-show="isScrollable && !hasScrolledToStart" x-on:click="scrollLeft"
                                    class="absolute left-0 z-10 p-2 transition-all rounded-full shadow-md text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                    style="transform: translateX(-50%);">
                                    <i class="fas fa-chevron-left"></i>
                                </button>

                                <!-- Tabs Container -->
                                <div x-ref="tabsContainer"
                                    class="flex gap-2 py-4 overflow-x-auto border-b text-md scrollbar-hide border-neutral-300 dark:border-neutral-700 scroll-smooth"
                                    style="scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
                                    @foreach($this->lists as $list)
                                    <div class="flex items-center px-4 py-2 transition-all rounded-lg text-md group text-nowrap hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                        :class="{
                                            'bg-neutral-100 dark:bg-neutral-800 border-b-2 border-neutral-600 dark:border-orange-500': selectedTab == {{ $list->id }},
                                            'bg-neutral-50 dark:bg-neutral-900': selectedTab != {{ $list->id }}
                                        }">
                                        <button type="button" x-on:click="selectedTab = {{ $list->id }}" wire:click.debounce.100ms="selectList({{ $list->id }})"
                                            class="mr-2 font-medium text-neutral-600 dark:text-neutral-300">
                                            {{ $list->name }}
                                            <span class="text-xs text-neutral-500 dark:text-neutral-400">({{ $list->emails_count }})</span>
                                        </button>
                                        <div class="flex items-center transition-opacity opacity-0 text-nowrap group-hover:opacity-100">
                                            <button type="button"
                                                x-on:click="$wire.set('listName', '{{ $list->name }}'); $dispatch('open-modal', 'edit-list-{{ $list->id }}')"
                                                class="ml-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" wire:click="deleteList({{ $list->id }})"
                                                wire:confirm="Are you sure you want to delete this list?"
                                                class="ml-2 text-neutral-400 hover:text-red-600 dark:hover:text-red-500">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Right Scroll Button -->
                                <button x-show="isScrollable && !hasScrolledToEnd" x-on:click="scrollRight"
                                    class="absolute right-0 z-10 p-2 transition-all rounded-full shadow-md text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                    style="transform: translateX(50%);">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>


                <!-- Show emails table only when a list is selected -->

                @if($selectedList)
                    <div wire:loading.remove wire:target="selectList">
                        @if($emails->count() > 0)
                            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                                <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
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
                                                    <th scope="col" class="p-4">Send Time</th>
                                                    <th scope="col" class="p-4">Sender Email</th>
                                                    <th scope="col" class="p-4">Log</th>
                                                    <th scope="col" class="p-4">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                                                @foreach($emails as $email)
                                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                                    <td class="p-4">
                                                        <input type="checkbox" wire:model.live="selectedEmails" value="{{ $email->id }}"
                                                            class="rounded">
                                                    </td>
                                                    <td class="p-4">{{ $email->email }}</td>
                                                    <td class="p-4">
                                                        @if($email->status !== 'NULL')
                                                        <span
                                                            class="inline-flex px-2 py-1 text-xs rounded-full
                                                                                                                                                                                        {{ $email->status === 'SENT' ? 'bg-green-100 text-green-800' :
                                                                                                                                                                                        ($email->status === 'FAIL' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                            {{ $email->status }}
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td class="p-4">{{ $email->send_time ? $email->send_time->format('d / m / Y') : '-' }}</td>
                                                    <td class="p-4">{{ $email->sender_email ?? '-' }}</td>
                                                    <td class="p-4">{{ Str::limit($email->log, 30) ?? '-' }}</td>
                                                    <td class="p-4">
                                                        <div class="flex space-x-2">
                                                            <button wire:click="clearSingleStatus({{ $email->id }})"
                                                                wire:confirm="Are you sure you want to clear this email status?"
                                                                class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                                                Clear Status
                                                            </button>

                                                            <button wire:click="deleteEmail({{ $email->id }})"
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
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500">
                                No emails found in this list.
                                @if(!$hasActiveJobsFlag && !$emailLimit['show'] && $user->balance('Subscribers Limit') != 0)
                                    <div class="mt-2">
                                        <x-primary-info-button href="{{ route('user.emails.create', ['list_id' => $selectedList]) }}" wire:navigate>
                                            Add New Emails
                                        </x-primary-info-button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div wire:loading.class.remove="hidden" wire:loading.class='flex' wire:target="selectList"  class="items-center justify-center hidden p-4">
                        <div class="w-8 h-8 border-4 border-blue-500 rounded-full animate-spin border-t-transparent"></div>
                    </div>
                    @else
                    <div class="p-8 text-center">
                        @if($this->lists->isEmpty())
                        <!-- No lists exist -->
                        <div class="flex flex-col items-center gap-4">
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
                        <div class="flex flex-col items-center gap-4">
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
                        <x-text-input wire:model="listName" id="listName" type="text" class="block w-full mt-1" />
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
                        <x-text-input wire:model="listName" id="listName" type="text" class="block w-full mt-1" />
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
