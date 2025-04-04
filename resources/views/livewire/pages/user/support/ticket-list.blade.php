<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col gap-3 justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            My Support Tickets
        </h2>
        <a href="{{ route('user.support.create') }}"
            class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white bg-sky-600 rounded-md border border-sky-300 transition duration-150 ease-in-out md:px-4 md:py-2 text-nowrap dark:bg-sky-900 group dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800'">
            New Ticket
        </a>
    </header>

    <!-- Search and Filters -->
    <div class="mb-6">
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:items-center">
            <div class="relative flex-1">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Search tickets by subject"
                    class="pl-10 w-full" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-primary-select-input wire:model.live="perPage" class="w-[150px]">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </x-primary-select-input>

                <div class="flex gap-2">
                    <button wire:click="$set('selectedTab', 'all')"
                        class="px-3 py-1 text-xs font-semibold rounded-md {{ $selectedTab === 'all' ? 'text-white bg-sky-600 dark:bg-sky-900' : 'bg-gray-100 text-gray-600' }} hover:bg-sky-700 dark:hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-500 hover:text-white">
                        All
                    </button>
                    <button wire:click="$set('selectedTab', 'open')"
                        class="px-3 py-1 text-xs font-semibold rounded-md {{ $selectedTab === 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600 hover:bg-yellow-50 hover:text-yellow-700' }} focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        Open
                    </button>
                    <button wire:click="$set('selectedTab', 'in_progress')"
                        class="px-3 py-1 text-xs font-semibold rounded-md {{ $selectedTab === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-700' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                        In Progress
                    </button>
                    <button wire:click="$set('selectedTab', 'closed')"
                        class="px-3 py-1 text-xs font-semibold rounded-md {{ $selectedTab === 'closed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600 hover:bg-green-50 hover:text-green-700' }} focus:outline-none focus:ring-2 focus:ring-green-500">
                        Closed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container with Relative Positioning -->
    <div class="overflow-hidden overflow-x-auto relative w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead
                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">Subject</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Timeline</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <a href="{{ route('user.support.ticket', $ticket) }}"
                            class="text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 text-nowrap">
                            {{ $ticket->subject }}
                        </a>
                    </td>
                    <td class="p-4">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full text-nowrap
                            {{ $ticket->status === 'open' ? 'text-yellow-800 bg-yellow-100 dark:text-yellow-300 dark:bg-yellow-900' : '' }}
                            {{ $ticket->status === 'in_progress' ? 'text-blue-800 bg-blue-100 dark:text-blue-300 dark:bg-blue-900' : '' }}
                            {{ $ticket->status === 'closed' ? 'text-green-800 bg-green-100 dark:text-green-300 dark:bg-green-900' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-col gap-1 text-xs">
                            <div>
                                <span class="font-medium">Submitted:</span><br>
                                {{ $ticket->created_at->format('d/m/Y H:i:s') }}<br>
                                <span class="text-neutral-500">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            @if($ticket->closed_at)
                            <div>
                                <span class="font-medium">Closed:</span><br>
                                {{ $ticket->closed_at->format('d/m/Y H:i:s') }}<br>
                                <span class="text-neutral-500">{{ $ticket->closed_at->diffForHumans() }}</span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-nowrap">
                        <div class="flex space-x-2">
                            <a href="{{ route('user.support.ticket', $ticket) }}"
                                class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-neutral-500 dark:text-neutral-400">
                        No tickets found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>