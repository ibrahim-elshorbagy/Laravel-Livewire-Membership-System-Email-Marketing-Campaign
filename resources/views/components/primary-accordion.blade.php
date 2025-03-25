@props([
'title' => '',
'isExpandedByDefault' => true
])

<div x-data="{ isExpanded: {{ json_encode($isExpandedByDefault) }} }">
    <button type="button"
        class="flex gap-2 sm:gap-4 justify-between items-center p-2 sm:p-4 w-full text-left bg-neutral-100 underline-offset-2 hover:bg-neutral-100/75 focus-visible:bg-neutral-100/75 focus-visible:underline focus-visible:outline-hidden dark:bg-neutral-700 dark:hover:bg-neutral-700/75 dark:focus-visible:bg-neutral-700/75"
        x-on:click="isExpanded = ! isExpanded"
        x-bind:class="isExpanded ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold' : 'text-onSurface dark:text-onSurfaceDark font-medium'">
        <span class="text-xs sm:text-sm md:text-base">{{ $title }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke="currentColor"
            class="transition size-4 sm:size-5 shrink-0" aria-hidden="true" x-bind:class="isExpanded ? 'rotate-180' : ''">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
    <div x-show="isExpanded" x-transition:enter="transition-all ease-out duration-300"
        x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-[1000px]"
        x-transition:leave="transition-all ease-in duration-200" x-transition:leave-start="opacity-100 max-h-[1000px]"
        x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden p-2 sm:p-4 text-xs sm:text-sm md:text-base text-pretty">
        {{ $slot }}
    </div>
</div>