@props(['checked' => false])

<label
    class="flex cursor-pointer items-center gap-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 [&:has(input:checked)]:text-neutral-900 dark:[&:has(input:checked)]:text-white [&:has(input:disabled)]:cursor-not-allowed [&:has(input:disabled)]:opacity-75">
    <div class="relative flex items-center">
        <input type="checkbox" @if($checked) checked @endif {{ $attributes->merge(['class' => 'peer relative size-4
        cursor-pointer appearance-none overflow-hidden rounded border border-neutral-300 bg-neutral-50
        before:content-[\'\'] before:absolute before:inset-0 before:scale-0 before:rounded-full before:transition
        before:duration-200 checked:border-black checked:before:scale-125 checked:before:bg-black focus:outline
        focus:outline-2 focus:outline-offset-2 focus:outline-neutral-800 checked:focus:outline-black
        active:outline-offset-0 disabled:cursor-not-allowed dark:border-neutral-700 dark:bg-neutral-900
        dark:checked:border-white dark:checked:before:bg-white dark:focus:outline-neutral-300
        dark:checked:focus:outline-white']) }}>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none"
            stroke-width="4"
            class="absolute invisible transition duration-200 delay-200 scale-0 -translate-x-1/2 -translate-y-1/2 pointer-events-none left-1/2 top-1/2 size-3 peer-checked:scale-100 text-neutral-100 peer-checked:visible dark:text-black">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
    </div>
    <span>{{ $slot }}</span>
</label>
