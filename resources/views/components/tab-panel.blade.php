@props([
'name',
'active' => false,
])

<div x-cloak x-show="selectedTab === '{{ $name }}'" role="tabpanel" id="panel-{{ $name }}"
    aria-labelledby="tab-{{ $name }}" class="focus:outline-none">
    {{ $slot }}
</div>
