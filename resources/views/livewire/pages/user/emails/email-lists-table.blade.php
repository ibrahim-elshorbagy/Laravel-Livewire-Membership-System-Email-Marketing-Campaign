<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Warning Alert -->
    <div class="mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                Mailing list
            </h2>
        </div>
        <div class="flex mt-4 md:mt-0 md:ml-4">
            @if(!$this->hasActiveJobs())
            @if(!$emailLimit['show'] && $user->balance('Subscribers Limit') != 0)
            <x-primary-info-button href="{{ route('user.emails.create') }}" wire:navigate>
                Add New Emails
            </x-primary-info-button>
            @endif
            @endif
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
            @if(!$this->hasActiveJobs())
            <!-- Per Page Actions -->
            <div class="w-full mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Current Page Actions:
                </span>
            </div>

            @if(count($selectedEmails) > 0)
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
            <div class="w-full h-px my-2 bg-gray-200 dark:bg-gray-700"></div>
            <div class="w-full">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
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
                Total Emails : <span class="font-bold">{{ $totalRecords }}</span> emails
            </li>
        </ul>
    </div>

    <div wire:poll.1000ms="$refresh">
    @if($queueStatus > 1)
        <div class="p-4 mb-6 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900 dark:border-blue-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="text-blue-600 fas fa-spinner fa-spin dark:text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        <i class="mr-1 fas fa-clock"></i> Jobs in Queue
                    </h3>
                    <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        <i class="mr-1 fas fa-info-circle"></i>
                        Your task is in queue position {{ $queueStatus }}. It will start automatically when
                        resources are available.
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($jobProgress->isNotEmpty())
        <div class="mb-6 space-y-4">
            @foreach($jobProgress as $progress)
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <i class="mr-1 fas fa-tasks"></i>
                        {{ ucwords(str_replace('_', ' ', $progress->job_type)) }}
                    </span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <i class="mr-1 fas fa-percentage"></i>
                        {{ number_format($progress->percentage, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                        style="width: {{ $progress->percentage }}%"></div>
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <i class="mr-1 fas fa-list-ol"></i>
                    {{ $progress->processed_items }} / {{ $progress->total_items }} items processed
                </div>
            </div>
            @endforeach
        </div>
        @endif
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
                        <span class="inline-flex px-2 py-1 text-xs rounded-full
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
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $emails->links() }}
    </div>
</div>
