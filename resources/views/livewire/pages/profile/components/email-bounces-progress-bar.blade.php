<div class="my-6" wire:poll.{{ $pollInterval }}ms="refreshProgress">
    @if($progressData['queueStatus'] > 0)
    <div class="p-4 mb-4 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900 dark:border-blue-800">
        <div class="flex items-center mb-2">
            <i class="mr-2 text-lg text-blue-600 fas fa-clock dark:text-blue-400"></i>
            <p class="text-blue-800 dark:text-blue-300">Please wait for the job processing to complete in the background. This process may take a few minutes, depending on the
            (Numbers of Emails). You may close the page while it processes.</p>
        </div>
        <div class="flex items-center">
            <i class="mr-2 text-lg text-blue-600 fas fa-list-ol dark:text-blue-400"></i>
            <p class="text-blue-800 dark:text-blue-300">Queue position: {{ $progressData['queueStatus'] }}</p>
        </div>
    </div>
    @endif

    @if($progressData['progress']->isNotEmpty())
    <div class="space-y-4">
        @foreach($progressData['progress'] as $progress)
        @if($progress->job_type === 'process_bounce_emails')
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="mr-1 fas fa-envelope-open"></i>
                    Bounce Check Progress
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ min(100, number_format($progress->percentage, 1)) }}%
                </span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full dark:bg-gray-700">
                <div class="h-2.5 bg-blue-600 rounded-full transition-all duration-300"
                    style="width: {{ min(100, $progress->percentage) }}%"></div>
            </div>
            <div class="flex flex-col mt-1 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <div>
                    <i class="mr-1 fas fa-envelope"></i>
                    {{ $progress->processed_items }} / {{ $progress->total_items }} emails processed
                </div>
                @if($progress->status === 'failed')
                <div class="text-red-600 dark:text-red-400">
                    <i class="mr-1 fas fa-exclamation-triangle"></i>
                    {{ $progress->error }}
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif
</div>
