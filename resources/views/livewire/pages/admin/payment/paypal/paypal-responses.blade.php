<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            PayPal Responses
        </h2>
        <div class="mt-4 md:mt-0">
            <button wire:click="deleteAll"
                wire:confirm="Are you sure you want to delete all PayPal responses? This action cannot be undone."
                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                Delete All Responses
            </button>
        </div>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.600ms="search"
                    placeholder="Search Transaction ID, User..." class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
                @if($search)
                <div class="flex absolute inset-y-0 right-0 items-center pr-3">
                    <button wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                    <option value="transaction_id">Sort by Transaction ID</option>
                    <option value="status">Sort by Status</option>
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
            @if(count($selectedResponses) > 0)
            <span class="text-sm font-medium">{{ count($selectedResponses) }} items selected</span>
            <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected responses?"
                class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                Delete Selected
            </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">
                        <input type="checkbox" wire:model.live="selectPage" class="rounded">
                    </th>
                    <th scope="col" class="p-4">User</th>
                    <th scope="col" class="p-4">Transaction ID</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Response Data</th>
                    <th scope="col" class="p-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($responses as $response)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <input type="checkbox" wire:model.live="selectedResponses" value="{{ $response->id }}"
                            class="rounded">
                    </td>
                    <td class="p-4">
                        @if($response->user)
                        <div class="flex gap-2 items-center w-max">
                            <img class="object-cover rounded-full size-10"
                                src="{{ $response->user->image_url ?? 'default-avatar.png' }}"
                                alt="{{ $response->user->full_name }}" />
                            <div class="flex flex-col">
                                <span class="text-neutral-900 dark:text-neutral-100">
                                    {{ $response->user->first_name }} {{ $response->user->last_name }} - ( {{
                                    $response->user->username }} )
                                </span>
                                <span class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                    {{ $response->user->email }}
                                </span>
                            </div>
                        </div>
                        @else
                        <span class="text-neutral-500">No user associated</span>
                        @endif
                    </td>
                    <td class="p-4">{{ $response->transaction_id }}</td>
                    <td class="p-4">
                        <span
                            class="inline-flex overflow-hidden rounded-lg px-1 py-0.5 text-xs font-medium {{ $response->status === 'success' ? 'text-green-300 bg-green-300/10' : 'text-red-500 bg-red-500/10' }}">
                            {{ ucfirst($response->status) }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="space-y-2">
                            @if(isset($response->response_data['error']))
                            <div class="flex items-center space-x-2">
                                <span
                                    class="inline-flex overflow-hidden px-1 py-0.5 text-xs font-medium text-red-500 rounded-lg bg-red-500/10">
                                    Error: {{ $response->response_data['error'] }}
                                </span>
                            </div>
                            @endif
                            @if(isset($response->response_data['message']))
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{
                                $response->response_data['message'] }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="p-4">{{ $response->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @if($responses->isEmpty())
    <div class="flex flex-col justify-center items-center p-6 text-center">
        <h3 class="mb-1 text-lg font-medium text-neutral-900 dark:text-neutral-100">No PayPal Responses Found</h3>
        <p class="text-neutral-500 dark:text-neutral-400">There are no PayPal responses recorded in the system.</p>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-4">
        {{ $responses->links() }}
    </div>
</div>