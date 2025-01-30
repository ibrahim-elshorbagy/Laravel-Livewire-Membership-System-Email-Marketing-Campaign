<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex flex-col items-center md:px-4 md:py-2 px-2 py-1 text-nowrap
    bg-neutral-600 dark:bg-neutral-900 border rounded-md group border-neutral-300 text-white
    dark:border-neutral-700 dark:text-neutral-300 font-semibold text-xs tracking-widest hover:bg-gray-700
    dark:hover:bg-gray-500 focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300
    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition
    ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
