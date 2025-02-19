<div wire:poll.{{ $pollInterval }}ms="updateJobProgress">
    @if($this->JobProgress['queueStatus'] > 1)
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
                    Your task is in queue position {{ $this->JobProgress['queueStatus'] }}. It will start automatically
                    when resources are available.
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($this->JobProgress['progress']->isNotEmpty())
    <div class="mb-6 space-y-4">
        @foreach($this->JobProgress['progress'] as $progress)
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="mr-1 fas fa-tasks"></i>
                    {{ ucwords(str_replace('_', ' ', $progress->job_type)) }}
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="mr-1 fas fa-percentage"></i>
                    {{ min(100, number_format($progress->percentage, 1)) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
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