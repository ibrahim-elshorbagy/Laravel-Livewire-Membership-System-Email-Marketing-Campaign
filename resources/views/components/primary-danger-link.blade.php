<a {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex flex-col items-center md:px-4 md:py-2 px-2 py-1 text-nowrap
    bg-red-600 dark:bg-red-700 border rounded-md group border-red-400 dark:border-red-600 text-white
    dark:text-red-100 font-semibold text-xs tracking-widest hover:bg-red-600 dark:hover:bg-red-800
    focus:bg-red-600 dark:focus:bg-red-800 active:bg-red-700 dark:active:bg-red-900
    focus:outline-none focus:ring-2 focus:ring-red-400 dark:focus:ring-red-500 focus:ring-offset-2
    dark:focus:ring-offset-red-900 transition
    ease-in-out duration-150']) }}>
    {{ $slot }}
</a>
