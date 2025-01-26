@props(['icon'])

<a {{ $attributes->merge([
    'class' => 'flex items-center rounded-md gap-2 px-2 py-1.5 text-sm font-medium text-neutral-600
    dark:text-neutral-300
    underline-offset-2 hover:bg-black/5 hover:text-neutral-900 dark:hover:bg-white/5 dark:hover:text-white
    focus-visible:underline focus:outline-none'
    ]) }}
    wire:current="text-neutral-900 dark:text-white bg-black/5 dark:bg-white/5">
    <span class="flex items-start gap-2">{{ $slot }}</span>
</a>
