<div wire:poll.{{ $pollInterval }}ms="refreshProgress">

    <!-- Show queue message -->
    @if($this->progressData['queueStatus'] > 0)
    <div class="p-4 mb-6 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900 dark:border-blue-800">
        <div class="flex items-center mb-2">
            <i class="mr-2 text-lg text-blue-600 fas fa-clock dark:text-blue-400"></i>
            <p class="text-blue-800 dark:text-blue-300">Please wait for the job processing to complete in the background. This process may take a few minutes, depending on the file
            size (Numbers of Emails). You may close the page while it processes.</p>
        </div>
        <div class="flex items-center">
            <i class="mr-2 text-lg text-blue-600 fas fa-list-ol dark:text-blue-400"></i>
            <p class="text-blue-800 dark:text-blue-300">Your task is in queue position {{ $progressData['queueStatus'] }}...</p>
        </div>
    </div>
    @endif

    @if($this->progressData['progress']->isNotEmpty())
    <div class="mb-6 space-y-4">
        @foreach($this->progressData['progress'] as $progress)
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="mr-1 fas fa-tasks"></i>
                    {{ ucwords(str_replace('_', ' ', $progress->job_type)) }}
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="mr-1 fas fa-percentage"></i>
                    {{ min(100, number_format($progress->percentage, 1)) }}%
                </span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full dark:bg-gray-700">
                <div class="h-2.5 bg-blue-600 rounded-full transition-all duration-300"
                    style="width: {{ min(100, $progress->percentage) }}%"></div>
            </div>
            <div class="flex flex-col mt-1 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <div>
                    <i class="mr-1 fas fa-list-ol"></i>
                    {{ $progress->processed_items }} / {{ $progress->total_items }} items processed
                    <span class="ml-1 text-yellow-600 dark:text-yellow-400">
                        <i class="mr-1 fas fa-info-circle"></i>
                        (Total is estimated)
                    </span>
                </div>
                @if($progress->percentage > 100)
                <div class="text-yellow-600 dark:text-yellow-400">
                    <i class="mr-1 fas fa-exclamation-triangle"></i>
                    More items found than initially estimated
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
