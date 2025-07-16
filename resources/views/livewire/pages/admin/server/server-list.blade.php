<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Sending bots Management
        </h2>
        <div class="flex flex-wrap justify-center gap-2 mt-2 md:justify-normal">

            <x-primary-info-link href="{{ route('admin.orphan-servers') }}" wire:navigate>
                Orphan Sending bots Report
            </x-primary-info-link>
            <x-primary-create-link href="{{ route('admin.servers.form') }}">
                Add Sending bots
            </x-primary-create-link>

        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search Sending bots, Users with any info"
                    class="w-full pl-10" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="name">Sort by Name</option>
                    <option value="last_access_time">Sort by Last Access</option>
                    <option value="current_quota">Sort by Quota</option>
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
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-4">
            @if(count($selectedServers) > 0)
            <span class="text-sm font-medium">{{ count($selectedServers) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected servers?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </button>
            <button type="button" x-on:click="$dispatch('open-modal', 'bulk-emails-count-modal')"
                class="px-3 py-1 text-sm text-white bg-blue-500 rounded-md hover:bg-blue-600">
                Update Emails Count
            </button>
            @endif
        </div>
    </div>

    <!-- Table Container with Relative Positioning -->
    <div class="relative w-full overflow-hidden overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">Name</th>
                    <th scope="col" class="p-4 w-[300px]">Note</th>
                    <th scope="col" class="p-4 w-[200px]">Assigned To</th>
                    <th scope="col" class="p-4">Quota</th>
                    <th scope="col" class="p-4">Emails Count</th>
                    <th scope="col" class="p-4">Dates </th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($servers as $server)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedServers" value="{{ $server->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4 text-sm">
                        @if ($server->is_orphan)
                        <span class="text-lg text-red-500">
                            *
                        </span>
                        @endif
                        {{ $server->name }}
                    </td>
                    <td class="p-4" wire:key="note-row-{{ $server->id }}-{{ $server->updated_at->timestamp }}">
                        <div x-data="{
                            isEditing: false,
                            tempNote: @js($server->admin_notes),
                            originalNote: @js($server->admin_notes),
                            serverId: @js($server->id),
                            init() {
                                // Restore edit state if this server was being edited
                                if (this.$wire.selectedServerId === this.serverId) {
                                    this.isEditing = true;
                                    this.tempNote = this.$wire.edit_admin_notes || this.originalNote;
                                }
                            },
                            startEdit() {
                                this.isEditing = true;
                                this.tempNote = this.originalNote;
                                this.$wire.set('selectedServerId', this.serverId, false);
                                this.$wire.set('edit_admin_notes', this.tempNote, false);

                                // Focus the textarea
                                this.$nextTick(() => {
                                    this.$el.querySelector('textarea')?.focus();
                                });
                            },
                            async saveEdit() {
                                // Update Livewire properties
                                this.$wire.set('edit_admin_notes', this.tempNote, false);
                                this.$wire.set('selectedServerId', this.serverId, false);

                                // Call save and wait for completion
                                await this.$wire.saveNote();

                                // Update local state only after successful save
                                this.originalNote = this.tempNote;
                                this.isEditing = false;
                            },
                            cancelEdit() {
                                this.isEditing = false;
                                this.tempNote = this.originalNote;
                                this.$wire.set('selectedServerId', null, false);
                            }
                        }">
                            <!-- Edit Mode -->
                            <div x-show="isEditing" class="flex items-center space-x-2">
                                <x-textarea-input class="w-full text-sm" x-model="tempNote" @keydown.enter.prevent="saveEdit()"
                                    @keydown.escape="cancelEdit()" />
                                <button @click="saveEdit()" class="text-green-500 hover:text-green-600">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button @click="cancelEdit()" class="text-red-500 hover:text-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- View Mode -->
                            <div x-show="!isEditing">
                                <div class="flex items-center space-x-2">
                                    <span x-text="originalNote"></span>
                                    <button @click="startEdit()" class="text-blue-500 hover:text-blue-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full px-4 py-2 text-left border rounded-md shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                @if($server->assignedUser)
                                <div class="flex items-center gap-2 w-max">
                                    <img class="object-cover rounded-full size-10"
                                        src="{{ $server->assignedUser->image_url ?? asset('default-avatar.png') }}"
                                        alt="{{ $server->assignedUser->first_name }} {{ $server->assignedUser->last_name }}" />
                                    <div class="flex flex-col">
                                        <span class="text-neutral-900 dark:text-neutral-100">
                                            {{ $server->assignedUser->first_name }} {{ $server->assignedUser->last_name
                                            }}
                                            - ({{ $server->assignedUser->username }})
                                        </span>
                                        <span class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                            {{ $server->assignedUser->email }}
                                        </span>
                                    </div>
                                </div>
                                @else
                                <span class="text-neutral-500 dark:text-neutral-400">Select User</span>
                                @endif
                            </button>

                            <!-- Dropdown positioned with fixed strategy -->
                            <div  x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="fixed z-[9999] w-96 bg-white rounded-md shadow-lg dark:bg-neutral-800" x-init="$watch('open', value => {
                                    if (value) {
                                        $nextTick(() => {
                                            const button = $el.previousElementSibling;
                                            const rect = button.getBoundingClientRect();
                                            const dropdown = $el;
                                            const dropdownHeight = dropdown.offsetHeight;
                                            const viewportHeight = window.innerHeight;

                                            // Position dropdown below or above the button based on available space
                                            if (rect.bottom + dropdownHeight > viewportHeight && rect.top > dropdownHeight) {
                                                dropdown.style.bottom = `${viewportHeight - rect.top}px`;
                                                dropdown.style.top = 'auto';
                                            } else {
                                                dropdown.style.top = `${rect.bottom}px`;
                                                dropdown.style.bottom = 'auto';
                                            }

                                            dropdown.style.left = `${rect.left}px`;
                                        });
                                    }
                                })">
                                <div class="p-2">
                                    <input type="text" wire:model.live.debounce.300ms="userSearch"
                                        class="w-full px-3 py-2 border rounded-md dark:bg-neutral-700 dark:border-neutral-600"
                                        placeholder="Search users...">

                                    <div class="overflow-y-auto mt-2 max-h-[300px]">
                                        <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700"
                                            wire:click="assignUser({{ $server->id }}, null); open = false">
                                            No User (Clear Selection)
                                        </div>
                                        @foreach($users as $user)
                                        <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700
                                            {{ $server->assigned_to_user_id == $user->id ? 'bg-sky-50 dark:bg-sky-900' : '' }}"
                                            wire:click="assignUser({{ $server->id }}, {{ $user->id }}); open = false">
                                            <div class="flex items-center gap-2 w-max">
                                                <img class="object-cover rounded-full size-10"
                                                    src="{{ $user->image_url ?? asset('default-avatar.png') }}"
                                                    alt="{{ $user->first_name }} {{ $user->last_name }}" />
                                                <div class="flex flex-col">
                                                    <span class="text-neutral-900 dark:text-neutral-100">
                                                        {{ $user->first_name }} {{ $user->last_name }}
                                                        - ({{ $user->username }})
                                                    </span>
                                                    <span
                                                        class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                                        {{ $user->email }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 text-xs">{{ $server->current_quota }}</td>
                    <td wire:key="emails_count-{{ $server->id }}-{{ $server->updated_at->timestamp }}" class="p-4 text-xs" x-data="{
                            isEditing: false,
                            tempCount:  @js($server->emails_count) ,
                            originalCount:  @js($server->emails_count) ,
                            serverId:  @js($server->id) ,
                            init() {
                                // Restore edit state if this server was being edited
                                if (this.$wire.selectedEmailsCountServerId === this.serverId) {
                                    this.isEditing = true;
                                    this.tempCount = this.$wire.tempEmailsCount || this.originalCount;
                                }
                            },
                            startEdit() {
                                this.isEditing = true;
                                this.tempCount = this.originalCount;
                                this.$wire.set('selectedEmailsCountServerId', this.serverId, false);
                                this.$wire.set('tempEmailsCount', this.tempCount, false);

                                // Focus the input after it appears
                                this.$nextTick(() => {
                                    this.$el.querySelector('input[type=number]')?.focus();
                                });
                            },
                            async saveEdit() {
                                if (this.tempCount >= 1 && this.tempCount <= 255) {
                                    // Update Livewire properties without re-render
                                    this.$wire.set('tempEmailsCount', this.tempCount, false);
                                    this.$wire.set('selectedEmailsCountServerId', this.serverId, false);

                                    // Call save and wait for completion
                                    await this.$wire.saveEmailsCount();

                                    // Update local state only after successful save
                                    this.originalCount = this.tempCount;
                                    this.isEditing = false;
                                }
                            },
                            cancelEdit() {
                                this.isEditing = false;
                                this.tempCount = this.originalCount;
                                this.$wire.set('selectedEmailsCountServerId', null, false);
                            }
                        }">
                        <!-- Edit Mode -->
                        <div x-show="isEditing"  class="flex items-center space-x-2">
                            <x-text-input type="number" min="1" max="255" class="w-20 text-sm" x-model="tempCount"
                                @keydown.enter.prevent="saveEdit()" @keydown.escape="cancelEdit()" />
                            <button @click="saveEdit()" class="text-green-500 hover:text-green-600">
                                <i class="fas fa-check"></i>
                            </button>
                            <button @click="cancelEdit()" class="text-red-500 hover:text-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- View Mode -->
                        <div x-show="!isEditing"  class="flex items-center space-x-2">
                            <span x-text="originalCount"></span>
                            <button @click="startEdit()" class="text-blue-500 hover:text-blue-600">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                    <td class="flex flex-col p-4 text-xs text-nowrap">
                        <span>Added At</span>

                        <span>{{ $server->created_at?->format('d/m/Y h:i:s A') }}</span>
                        <span>Last Access</span>
                        <span>{{ $server->last_access_time?->format('d/m/Y h:i:s A') }}</span>
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            {{-- <div class="flex items-center gap-2">
                                <button type="button"
                                    x-on:click="$dispatch('open-modal', 'edit-note-modal'); $wire.selectedServerId = {{ $server->id }}; $wire.edit_admin_notes = `{{ $server->admin_notes ?? '' }}`"
                                    class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <i class="fa-solid fa-note-sticky"></i>
                                </button>
                            </div> --}}
                            <a href="{{ route('admin.site-api-requests') }}?search={{ $server->name }}" wire:navigate
                                class="inline-flex items-center gap-2 px-2 py-1 text-xs text-purple-500 rounded-md text-nowrap bg-purple-500/10 hover:bg-purple-500/20">
                                API Requests
                            </a>
                            <a href="{{ route('admin.servers.form', $server->id) }}"
                                class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                Edit
                            </a>

                            <button wire:click="deleteServer({{ $server->id }})"
                                wire:confirm="Are you sure you want to delete this server?"
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

    <!-- Single Reusable Edit Email Modal -->
    <x-modal name="edit-note-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium">Admin Note</h2>
            <form wire:submit.prevent="saveNote" class="mt-4">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="edit_admin_notes" value="Admin Note" />
                        <x-textarea-input wire:model="edit_admin_notes" id="edit_admin_notes" type="text"
                            class="block w-full mt-1" />
                        <x-input-error :messages="$errors->get('editEmail')" class="mt-2" />
                    </div>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-note-modal')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        Update
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Bulk Update Emails Count Modal -->
    <x-modal name="bulk-emails-count-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium">Update Emails Count for Selected Servers</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                This will update the emails count for {{ count($selectedServers) }} selected server(s).
            </p>
            <form wire:submit.prevent="bulkUpdateEmailsCount" class="mt-4">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="bulkEmailsCount" value="Emails Count" />
                        <x-text-input wire:model="bulkEmailsCount" id="bulkEmailsCount" type="number" min="1" max="255"
                            class="block w-full mt-1" placeholder="Enter emails count" />
                        <x-input-error :messages="$errors->get('bulkEmailsCount')" class="mt-2" />
                    </div>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'bulk-emails-count-modal')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        Update All Selected
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $servers->links() }}
    </div>
</div>
