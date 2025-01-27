@props(['disabled' => false])

<div class="relative">

    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
        'class' => 'w-full appearance-none rounded-lg bg-neutral-100 px-4 py-2 text-sm
        focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black
        disabled:cursor-not-allowed disabled:opacity-75 dark:bg-neutral-800/50
        dark:focus-visible:outline-orange-500 dark:text-neutral-200'
        ]) !!}>
        {{ $slot }}
    </select>
</div>
