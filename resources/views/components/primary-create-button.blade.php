<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex flex-col items-center md:px-4 md:py-2 px-2 py-1 text-nowrap
    bg-green-600 dark:bg-green-900 border rounded-md group border-green-300 text-white
    dark:border-green-700 dark:text-green-300 font-semibold text-xs tracking-widest hover:bg-green-700
    dark:hover:bg-green-100 focus:bg-green-700 dark:focus:bg-green-100 active:bg-green-900 dark:active:bg-green-300
    focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-green-800 transition
    ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
