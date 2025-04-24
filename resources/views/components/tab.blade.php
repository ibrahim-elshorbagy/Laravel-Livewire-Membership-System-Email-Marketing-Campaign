@props([
'name',
'active' => false
])
<div data-tab-name="{{ $name }}"
    class="flex items-center px-4 py-2 rounded-lg transition-all text-md group text-nowrap hover:bg-neutral-100 dark:hover:bg-neutral-800"
    :class="{
        'bg-neutral-100 dark:bg-neutral-800 border-b-2 border-neutral-600 dark:border-orange-500': selectedTab === '{{ $name }}',
        'bg-neutral-50 dark:bg-neutral-900': selectedTab !== '{{ $name }}'
    }">
    <button type="button" x-on:click="selectTab('{{ $name }}')" :aria-selected="selectedTab === '{{ $name }}'"
        :tabindex="selectedTab === '{{ $name }}' ? '0' : '-1'"
        class="font-medium text-neutral-600 dark:text-neutral-300" role="tab" :id="'tab-{{ $name }}'"
        :aria-controls="'panel-{{ $name }}'">
        {{ $slot }}
    </button>
    {{ $actions ?? '' }}
</div>
