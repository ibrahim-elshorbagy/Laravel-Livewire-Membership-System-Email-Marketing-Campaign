@props([
'icon' => null,
'label' => 'Actions Menu',
'links' => []
])

<div x-data="{ isOpen: false, openedWithKeyboard: false }" class="relative w-fit"
    x-on:keydown.esc.window="isOpen = false, openedWithKeyboard = false">
    <!-- Toggle Button -->
    <button type="button" x-on:click="isOpen = ! isOpen"
        class="inline-flex gap-2 items-center px-2 py-1.5 text-sm font-medium tracking-wide whitespace-nowrap rounded-md transition text-neutral-600 dark:text-neutral-300 hover:bg-black/5 hover:text-neutral-900 dark:hover:bg-white/5"
        aria-haspopup="true" x-on:keydown.space.prevent="openedWithKeyboard = true"
        x-on:keydown.enter.prevent="openedWithKeyboard = true" x-on:keydown.down.prevent="openedWithKeyboard = true"
        x-bind:aria-expanded="isOpen || openedWithKeyboard">
        @if($icon)
        <i class="{{ $icon }} mr-2"></i>
        @endif
        {{ $label }}
        <i class="ml-2 transition-transform fas fa-chevron-down" x-bind:class="{ 'rotate-180': isOpen }"></i>
    </button>

    <!-- Dropdown Menu -->
    <div x-cloak x-show="isOpen || openedWithKeyboard" x-transition x-trap="openedWithKeyboard"
        x-on:click.outside="isOpen = false, openedWithKeyboard = false" x-on:keydown.down.prevent="$focus.wrap().next()"
        x-on:keydown.up.prevent="$focus.wrap().previous()"
        class="flex overflow-hidden absolute left-0 top-11 z-50 flex-col bg-white rounded-md border shadow-lg w-fit min-w-48 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700"
        role="menu">
        {{ $slot }}
    </div>
</div>
