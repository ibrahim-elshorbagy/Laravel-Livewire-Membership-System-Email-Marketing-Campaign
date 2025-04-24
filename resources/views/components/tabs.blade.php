@props([
'selectedTab' => null,
'defaultTab' => null,
'tabAlignment' => 'center' // options: left, center, right
])

<div x-data="{
    selectedTab: @entangle('selectedList').live,
    scrollContainer: null,
    isScrollable: false,
    hasScrolledToEnd: false,
    hasScrolledToStart: true,

    init() {
        this.scrollContainer = this.$refs.tabsContainer;
        this.checkScroll();
        window.addEventListener('resize', () => this.checkScroll());
        this.scrollContainer.addEventListener('scroll', () => this.checkScroll());

        // Add Livewire event listener
        $wire.on('tabSelected', (listId) => {
            this.selectedTab = listId;
        });

    },

    checkScroll() {
        if (!this.scrollContainer) return;
        this.isScrollable = this.scrollContainer.scrollWidth > this.scrollContainer.clientWidth;
        this.hasScrolledToStart = this.scrollContainer.scrollLeft <= 0;
        this.hasScrolledToEnd = this.scrollContainer.scrollLeft + this.scrollContainer.clientWidth >= this.scrollContainer.scrollWidth;
    },

    scrollLeft() {
        this.scrollContainer.scrollBy({ left: -200, behavior: 'smooth' });
    },

    scrollRight() {
        this.scrollContainer.scrollBy({ left: 200, behavior: 'smooth' });
    },

    selectTab(tabName) {
        this.selectedTab = tabName;
        // Trigger Livewire action
        {{-- $wire.selectedList(tabName); --}}
        // Dispatch a custom event that can be listened to
        this.$dispatch('tab-selected', tabName);
    }
}" class="w-full">
    <div class="flex relative items-center">
        <!-- Left Scroll Button -->
        <button x-cloak x-show="isScrollable && !hasScrolledToStart" x-on:click="scrollLeft"
            class="absolute left-0 z-10 p-2 rounded-full shadow-md transition-all text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
            style="transform: translateX(-50%);" aria-label="Scroll left">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Tabs Container -->
        <div x-ref="tabsContainer" x-on:keydown.right.prevent="$focus.wrap().next()"
            x-on:keydown.left.prevent="$focus.wrap().previous()"
            class="flex overflow-x-auto gap-2 py-4 border-b border-neutral-300 dark:border-neutral-700 scroll-smooth {{ $tabAlignment === 'center' ? 'justify-center' : ($tabAlignment === 'right' ? 'justify-end' : '') }}"
            role="tablist" aria-label="Tab navigation"
            style="scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
            {{ $tabs }}
        </div>

        <!-- Right Scroll Button -->
        <button x-cloak x-show="isScrollable && !hasScrolledToEnd" x-on:click="scrollRight"
            class="absolute right-0 z-10 p-2 rounded-full shadow-md transition-all text-neutral-600 bg-neutral-50 dark:bg-neutral-900 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
            style="transform: translateX(50%);" aria-label="Scroll right">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Tab content wrapper with loading states -->
    <div class="relative py-4">
        <!-- Content visible when not loading -->
        <div wire:loading.remove wire:target="selectedList">
            {{ $content }}
        </div>

        <!-- Loading indicator - only appears during loading -->
        <div wire:loading.class.remove="hidden" wire:loading.class='flex' wire:target="selectedList"
            class="hidden justify-center items-center p-4">
            <div class="w-8 h-8 rounded-full border-4 border-blue-500 animate-spin border-t-transparent"></div>
        </div>
    </div>
</div>
