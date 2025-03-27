<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div class="flex flex-col gap-2 justify-between items-start mb-4 md:flex-row">
            <div>
                <h2 class="text-xl font-semibold md:text-2xl text-neutral-800 dark:text-neutral-200">Subject: {{$ticket->subject }}</h3>
                <div class="flex flex-wrap gap-1 items-center my-3 text-xs md:gap-2 md:text-sm">
                    <span>Status:</span>
                    <span class="px-2 md:px-3 py-0.5 md:py-1 rounded-full
                        @if($ticket->status === 'open') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @endif">
                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                    </span>
                    <span>{{ $ticket->closed_at?->format('d/m/Y h:i:s A') }}</span>
                </div>
                <div class="pt-4 border-t border-neutral-200 dark:border-neutral-600">
                    <dl class="text-sm divide-y divide-neutral-200 dark:divide-neutral-600">
                        <div class="grid grid-cols-2 gap-4 py-3">
                            <span class="text-neutral-600 dark:text-neutral-400">Ticket Submitted</span>
                            <span class="text-xs text-neutral-800 dark:text-neutral-200 md:text-small">{{ $ticket->created_at->format('d/m/Yh:i:s A') }} - {{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                        @isset($ticket->closed_at)
                        <div class="grid grid-cols-2 gap-4 py-3">
                            <span class="text-neutral-600 dark:text-neutral-400">Closed</span>
                            <span class="text-xs text-neutral-800 dark:text-neutral-200 md:text-small">{{ $ticket->closed_at?->format('d/m/Y h:i:s A') }} - {{ $ticket->closed_at?->diffForHumans() }}</span>
                        </div>
                        @endisset
                    </dl>
                </div>
            </div>
            <div class="flex gap-4 items-center self-center mb-2 md:self-end">
                <x-primary-info-button href="{{ route('user.support.tickets') }}" wire:navigate>
                    Back To Tickets
                </x-primary-info-button>
            </div>
        </div>

        <div class="pt-6 border-t border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-200">Conversation</h3>
            <livewire:components.support.chat-component :ticket="$ticket" />
        </div>


    </div>
</div>
