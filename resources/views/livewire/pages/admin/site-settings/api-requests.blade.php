<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            API Requests
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-danger-button wire:click="deleteAll"
                wire:confirm="Are you sure you want to delete all API requests? This action cannot be undone.">
                Delete All Requests
            </x-primary-danger-button>
            <x-primary-info-button type="button" x-on:click="$dispatch('open-modal', 'bulk-delete-modal');">
                Bulk Delete Modal
            </x-primary-info-button>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search ServerId, Errors..."
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="userSearch" placeholder="Search User Username, Name"
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="serverid">Sort by Server ID</option>
                    <option value="request_time">Sort by Date</option>
                    <option value="execution_time">Sort by Duration</option>
                    <option value="status">Sort by Status</option>
                    <option value="error_number">Sort by Error Message</option>
                </x-primary-select-input>

                <x-primary-select-input wire:model.live="sortDirection" class="w-full sm:w-32">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </x-primary-select-input>
                <x-primary-select-input wire:model.live="status" class="w-full sm:w-32">
                    <option value="all">All</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
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
            @if(count($selectedRequests) > 0)
            <span class="text-sm font-medium">{{ count($selectedRequests) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected requests?"
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
                    <th scope="col" class="p-4 w-12">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4 min-w-96">Server ID</th>
                    <th scope="col" class="p-4 w-3 text-center">Status</th>
                    <th scope="col" class="p-4 min-w-96">Error Message</th>
                    <th scope="col" class="p-4 w-3">Duration</th>
                    <th scope="col" class="p-4 w-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($requests as $request)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedRequests" value="{{ $request->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4">
                        <div class="space-y-2">
                            <!-- Redesigned Server ID section -->
                            <div class="flex items-center">
                                <span
                                    class="px-2 py-1 font-medium text-blue-700 bg-blue-50 rounded dark:bg-blue-900 dark:text-blue-100">
                                    {{ $request->serverid }}
                                </span>
                            </div>

                            @if($request->server)
                            <div class="flex flex-col text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-neutral-500">Last Access:</span>
                                    <span>{{ $request->server->last_access_time ?
                                        $request->server->last_access_time->format('d/m/Y H:i:s') : 'Never' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-neutral-500">Quota:</span>
                                    <span>{{ $request->server->current_quota ?? 'N/A' }}</span>
                                </div>



                                @if($request->server->assignedUser)
                                <div
                                    class="p-2 mt-2 rounded border bg-neutral-100 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700">
                                    <div class="flex justify-between items-center">
                                        <div class="pr-2 truncate">
                                            <div class="text-xs font-medium">{{
                                                $request->server->assignedUser->first_name }} {{
                                                $request->server->assignedUser->last_name }}</div>
                                            <div class="text-xs truncate text-neutral-500">{{
                                                $request->server->assignedUser->username }}</div>
                                        </div>
                                        <x-primary-info-button
                                            onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                            wire:click="impersonateUser({{ $request->server->assignedUser->id }})"
                                            class="px-2 py-1 text-xs">
                                            Login
                                        </x-primary-info-button>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-center">
                        <span
                            class="px-2 py-1 text-xs font-medium rounded-full {{ $request->status === 'success' ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="overflow-hidden p-4">
                        @if($request->status === 'failed' && $request->error_data)
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <span
                                    class="px-2 py-1 text-xs font-medium text-red-800 truncate bg-red-100 rounded-full">
                                    Error #{{ $request->error_number }}: {{ $request->error }}
                                </span>
                            </div>
                            <p class="text-sm truncate text-neutral-600 dark:text-neutral-400"
                                title="{{ $request->message }}">
                                {{ $request->message }}
                            </p>
                        </div>
                        @else
                        <span class="text-neutral-500">-</span>
                        @endif
                    </td>
                    <td class="p-4">{{ number_format($request->execution_time, 3) }}s</td>
                    <td class="p-4">{{ $request->request_time->format('d/m/Y H:i:s A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @if($requests->isEmpty())
    <div class="flex flex-col justify-center items-center p-6 text-center">
        <h3 class="mb-1 text-lg font-medium text-neutral-900 dark:text-neutral-100">No API Requests Found</h3>
        <p class="text-neutral-500 dark:text-neutral-400">There are no API requests recorded in the system.</p>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-4">
        {{ $requests->links(data: ['scrollTo' => false]) }}
    </div>



    <!-- Bulk Delete Modal with Full Error Message Display -->

    <x-modal name="bulk-delete-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium">Bulk Delete API Requests</h2>
            <form wire:submit.prevent="bulkDeleteFiltered" class="mt-4">
                <div class="space-y-4">
                    <!-- Select Status -->
                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-primary-select-input wire:model="status" id="status" class="block mt-1 w-full">
                            <option value="all">All</option>
                            <option value="failed">Failed</option>
                            <option value="success">Success</option>
                        </x-primary-select-input>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Select Error Number -->
                    <div>
                        <x-input-label for="error_number" value="Error Number" />
                        <x-primary-select-input wire:model="selectedErrorNumber" id="error_number"
                            class="block mt-1 w-full">
                            <option value="">All</option>
                            @foreach ($errorData as $errorNumber => $message)
                            <option value="{{ $errorNumber }}">{{ 'Error #'. $errorNumber . " : ". $message }}</option>
                            @endforeach
                        </x-primary-select-input>
                        <x-input-error :messages="$errors->get('selectedErrorNumber')" class="mt-2" />
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="dateFrom" value="From Date" />
                            <x-text-input type="datetime-local" wire:model="dateFrom" id="dateFrom"
                                class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('dateFrom')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dateTo" value="To Date" />
                            <x-text-input type="datetime-local" wire:model="dateTo" id="dateTo"
                                class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('dateTo')" class="mt-2" />
                        </div>
                    </div>

                </div>

                <div class="flex justify-end mt-6 space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'bulk-delete-modal')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        Delete Filtered
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
