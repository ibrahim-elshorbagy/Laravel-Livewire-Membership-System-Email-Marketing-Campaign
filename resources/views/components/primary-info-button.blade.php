<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex flex-col items-center md:px-4 md:py-2 px-2 py-1 text-nowrap
    bg-sky-600 dark:bg-sky-900 border rounded-md group border-sky-300 text-white
    dark:border-sky-700 dark:text-sky-300 font-semibold text-xs tracking-widest hover:bg-sky-700
    dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300
    focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800 transition
    ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
