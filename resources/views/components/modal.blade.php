@props([
'name',
'show' => false,
'maxWidth' => '2xl'
])

@php
$maxWidth = [
'sm' => 'sm:max-w-sm',
'md' => 'sm:max-w-md',
'lg' => 'sm:max-w-lg',
'xl' => 'sm:max-w-xl',
'2xl' => 'sm:max-w-2xl',
'3xl' => 'sm:max-w-3xl',
'4xl' => 'sm:max-w-4xl',
'5xl' => 'sm:max-w-5xl',
'6xl' => 'sm:max-w-6xl',
'7xl' => 'sm:max-w-7xl',
][$maxWidth];
@endphp

<div x-data="modalInstance(@js($show))" x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null" x-show="show"
    class="overflow-y-auto fixed inset-0 z-50" style="display: {{ $show ? 'block' : 'none' }};">

    <!-- Backdrop -->
    <div x-show="show" class="fixed inset-0 transition-all transform" x-on:click="show = false"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 opacity-75 bg-neutral-300 dark:bg-neutral-700"></div>
    </div>

    <!-- Modal Content -->
    <div class="flex justify-center items-center p-4 min-h-full">
        <div x-show="show"
            class="bg-neutral-50 dark:bg-neutral-900 rounded-lg overflow-hidden shadow-xl transform transition-all w-full {{ $maxWidth }}"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
    function modalInstance(initialShow) {
    return {
        show: initialShow,
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
            return [...this.$el.querySelectorAll(selector)]
                .filter(el => !el.hasAttribute('disabled'));
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
        init() {
            this.$watch('show', value => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');
                    if (this.$el.hasAttribute('focusable')) {
                        setTimeout(() => this.firstFocusable().focus(), 100);
                    }
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                }
            });

            // Close on escape
            this.$el.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.show = false;
                if (e.key === 'Tab') {
                    e.preventDefault();
                    e.shiftKey ? this.prevFocusable().focus() : this.nextFocusable().focus();
                }
            });
        }
    };
}
</script>
