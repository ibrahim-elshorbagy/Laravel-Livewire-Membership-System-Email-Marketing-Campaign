<div class="flex-inline">
    <button
        @click="darkMode = !darkMode"
        class="flex justify-center items-center p-1.5 w-8 h-8 rounded-full transition-colors duration-200 focus:outline-none"
        :class="darkMode
            ? 'bg-neutral-800 text-neutral-200 hover:bg-neutral-700 hover:text-white'
            : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 hover:text-neutral-900'">
        <i class="fa-regular" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
    </button>
</div>
