<div class="flex items-center gap-2 p-2 rounded-md hover:bg-black/5 dark:hover:bg-white/5">
    <img src="{{ Auth::user()->image_url }}" class="object-cover rounded-md size-8" alt="avatar">
    <div class="hidden md:block">
        <span class="text-sm font-bold text-neutral-900 dark:text-white">{{ Auth::user()->first_name }}</span>
    </div>
</div>
