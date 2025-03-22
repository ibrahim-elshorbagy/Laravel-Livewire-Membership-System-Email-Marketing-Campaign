<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div class="flex flex-col md:flex-row justify-between items-start gap-2 mb-4">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold text-neutral-800 dark:text-neutral-200">Subject: {{$ticket->subject }}</h3>
                <div class="flex flex-wrap my-3 gap-1 md:gap-2 items-center text-xs md:text-sm">
                    <span>Status:</span>
                    <span class="px-2 md:px-3 py-0.5 md:py-1 rounded-full
                        @if($ticket->status === 'open') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                        @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                        @endif">
                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-4 mb-2 md:self-end self-center">
                <x-primary-info-button href="{{ route('user.support.tickets') }}" wire:navigate>
                    Back To Tickets
                </x-primary-info-button>
            </div>
        </div>

        <div class="mb-8 text-neutral-600 dark:text-neutral-400 no-tailwindcss-support-display border-t border-neutral-200 dark:border-neutral-600">
            {!! $ticket->message !!}
        </div>

        @if($ticket->admin_response)
        <div class="pt-6 mb-8 border-t border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-200">Admin Response</h3>
            <div class="mb-8 text-neutral-600 dark:text-neutral-400 no-tailwindcss-support-display border-t border-neutral-200 dark:border-neutral-600">{!!
                $ticket->admin_response !!}
            </div>
        </div>
        @endif

        <div class="pt-4 border-t border-neutral-200 dark:border-neutral-600">
            <dl class="text-sm divide-y divide-neutral-200 dark:divide-neutral-600">
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Ticket Submitted</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->created_at->format('d/m/Y h:i:s A') }}</dd>
                </div>
                @isset($ticket->closed_at)
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Closed</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->closed_at?->format('d/m/Y h:i:s A') }}</dd>
                </div>
                @endisset
                @isset($ticket->responded_at)
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Responded</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->responded_at?->format('d/m/Y h:i:s A') }}</dd>
                </div>
                @endisset
            </dl>
        </div>
    </div>
</div>