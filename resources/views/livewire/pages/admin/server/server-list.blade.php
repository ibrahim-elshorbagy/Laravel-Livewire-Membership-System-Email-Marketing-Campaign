<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Servers Management
        </h2>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.servers.form') }}"
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white transition duration-150 ease-in-out border rounded-md cursor-pointer md:px-4 md:py-2 text-nowrap bg-sky-600 dark:bg-sky-900 group border-sky-300 dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Add New Server
            </a>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search servers, Users with any info"
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
            @endif
        </div>
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
                    <th scope="col" class="p-4">Name</th>
                    <th scope="col" class="p-4">Assigned To</th>
                    <th scope="col" class="p-4">Last Access</th>
                    <th scope="col" class="p-4">Quota</th>
                    <th scope="col" class="p-4">Admin Notes</th>
                    <th scope="col" class="p-4">Added At</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($servers as $server)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedServers" value="{{ $server->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4">{{ $server->name }}</td>
                    <td class="p-4">
                        @if($server->assignedUser)
                        <div class="flex items-center gap-2 w-max">
                            <img class="object-cover rounded-full size-10"
                                src="{{ $server->assignedUser->image_url ?? asset('default-avatar.png') }}"
                                alt="{{ $server->assignedUser->first_name }} {{ $server->assignedUser->last_name }}" />
                            <div class="flex flex-col">
                                <span class="text-neutral-900 dark:text-neutral-100">
                                    {{ $server->assignedUser->first_name }} {{ $server->assignedUser->last_name }}
                                    - ({{ $server->assignedUser->username }})
                                </span>
                                <span class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                    {{ $server->assignedUser->email }}
                                </span>
                            </div>
                        </div>
                        @else
                        <span class="text-neutral-500 dark:text-neutral-400">Not Assigned</span>
                        @endif
                    </td>
                    <td class="p-4">{{ $server->last_access_time?->format('d / m / Y') ?? '' }}</td>
                    <td class="p-4">{{ $server->current_quota }}</td>
                    <td class="p-4">{{ Str::limit($server->admin_notes, 30) }}</td>
                    <td class="p-4">{{ $server->created_at?->format('d / m / Y') }}</td>
                    <td class="p-4">
                        <div class="flex space-x-2">
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

    <!-- Pagination -->
    <div class="mt-4">
        {{ $servers->links() }}
    </div>
</div>
