@props(['class' => 'text-black dark:text-white text-xl'])

<i {{ $attributes->merge(['class' => 'fa-solid fa-bell ' . $class]) }}></i>
