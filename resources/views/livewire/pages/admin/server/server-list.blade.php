<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Servers Management
        </h2>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.servers.form') }}"
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white bg-sky-600 rounded-md border border-sky-300 transition duration-150 ease-in-out cursor-pointer md:px-4 md:py-2 text-nowrap dark:bg-sky-900 group dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Add New Server
            </a>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search servers, Users with any info"
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
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
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
            @if(count($selectedServers) > 0)
            <span class="text-sm font-medium">{{ count($selectedServers) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected servers?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </button>
            @endif
        </div>
    </div>

    <!-- Table Container with Relative Positioning -->
    <div class="overflow-hidden overflow-x-auto relative w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">Name</th>
                    <th scope="col" class="p-4">Assigned To</th>
                    <th scope="col" class="p-4">Last Access</th>
                    <th scope="col" class="p-4">Quota</th>
                    <th scope="col" class="p-4">Added At</th>
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
                    <td class="p-4">{{ $server->name }}</td>
                    <td class="p-4">
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open"
                                class="px-4 py-2 w-full text-left rounded-md border shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                @if($server->assignedUser)
                                <div class="flex gap-2 items-center w-max">
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
                            <div x-show="open" @click.outside="open = false"
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
                                        class="px-3 py-2 w-full rounded-md border dark:bg-neutral-700 dark:border-neutral-600"
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
                                            <div class="flex gap-2 items-center w-max">
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
                    <td class="p-4">{{ $server->last_access_time?->format('d/m/Y h:i:s A')?? '' }}</td>
                    <td class="p-4">{{ $server->current_quota }}</td>
                    <td class="p-4">{{ $server->created_at?->format('d/m/Y h:i:s A') }}</td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            <div class="flex gap-2 items-center">
                                <button type="button"
                                    x-on:click="$dispatch('open-modal', 'edit-note-modal'); $wire.selectedServerId = {{ $server->id }}; $wire.edit_admin_notes = `{{ $server->admin_notes ?? '' }}`"
                                    class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <i class="fa-solid fa-note-sticky"></i>
                                </button>
                            </div>
                            <a href="{{ route('admin.site-api-requests') }}?search={{ $server->name }}" wire:navigate
                                class="inline-flex gap-2 items-center px-2 py-1 text-xs text-purple-500 rounded-md bg-purple-500/10 hover:bg-purple-500/20">
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
                            class="block mt-1 w-full" />
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
    <!-- Pagination -->
    <div class="mt-4">
        {{ $servers->links() }}
    </div>
</div>