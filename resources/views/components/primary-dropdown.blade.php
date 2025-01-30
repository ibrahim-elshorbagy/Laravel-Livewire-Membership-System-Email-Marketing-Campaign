@props([
'icon' => null,
'label' => 'Actions Menu',
'links' => []
])

<div x-data="{ isOpen: false, openedWithKeyboard: false }" class="relative w-fit"
    x-on:keydown.esc.window="isOpen = false, openedWithKeyboard = false">
    <!-- Toggle Button -->
    <button type="button" x-on:click="isOpen = ! isOpen"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium tracking-wide transition rounded-md text-neutral-600 dark:text-neutral-300 whitespace-nowrap hover:bg-neutral-200 dark:hover:bg-neutral-700 "
        aria-haspopup="true" x-on:keydown.space.prevent="openedWithKeyboard = true"
        x-on:keydown.enter.prevent="openedWithKeyboard = true" x-on:keydown.down.prevent="openedWithKeyboard = true"
        x-bind:aria-expanded="isOpen || openedWithKeyboard">
        @if($icon)
        <i class="{{ $icon }} mr-2"></i>
        @endif
        {{ $label }}
        {{-- <i class="ml-2 fas fa-chevron-down"></i> --}}
    </button>

    <!-- Dropdown Menu -->
    <div x-cloak x-show="isOpen || openedWithKeyboard" x-transition x-trap="openedWithKeyboard"
        x-on:click.outside="isOpen = false, openedWithKeyboard = false" x-on:keydown.down.prevent="$focus.wrap().next()"
        x-on:keydown.up.prevent="$focus.wrap().previous()"
        class="absolute left-0 z-50 flex flex-col overflow-hidden bg-white border rounded-md shadow-lg top-11 w-fit min-w-48 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700"
        role="menu">
        {{ $slot }}
    </div>
</div>
