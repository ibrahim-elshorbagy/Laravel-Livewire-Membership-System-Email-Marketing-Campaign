@props(['disabled' => false])

<div class="relative">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
        class="absolute text-gray-500 pointer-events-none right-4 top-2 size-5 dark:text-gray-400">
        <path fill-rule="evenodd"
            d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
            clip-rule="evenodd" />
    </svg>
    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' => 'w-full appearance-none rounded-lg bg-neutral-100 px-4 py-2 text-sm
        focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black
        disabled:cursor-not-allowed disabled:opacity-75 dark:bg-neutral-800/50
        dark:focus-visible:outline-orange-500 dark:text-neutral-200'
        ]) !!}>
        {{ $slot }}
    </select>
</div>
