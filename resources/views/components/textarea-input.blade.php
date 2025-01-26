@props(['disabled' => false])

<textarea rows="5" @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-md border border-neutral-300 bg-neutral-50
px-2 py-2 text-sm dark:text-white
disabled:cursor-not-allowed
disabled:opacity-75 dark:border-neutral-700 dark:bg-neutral-900/50']) }}>
</textarea>
