<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.getItem('dark') === 'true'}"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val))" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LivewireSaaS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarIsOpen: false }" class="relative flex flex-col w-full md:flex-row">

        <!-- Mobile sidebar overlay -->
        <div x-cloak x-show="sidebarIsOpen" class="fixed inset-0 z-20 bg-neutral-950/10 backdrop-blur-sm md:hidden"
            aria-hidden="true" x-on:click="sidebarIsOpen = false" x-transition.opacity></div>

        <!-- Mobile sidebar -->
        <nav x-cloak
            class="fixed left-0 z-30 flex flex-col p-4 transition-transform duration-300 border-r h-svh w-60 shrink-0 border-neutral-300 bg-neutral-50 md:hidden md:w-64 md:translate-x-0 dark:border-neutral-700 dark:bg-neutral-900"
            x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'">
            <a href="{{ route('welcome') }}" class="w-12 mb-4 ml-2 text-2xl font-bold text-neutral-900 dark:text-white"
                wire:navigate>
                <x-application-logo />
            </a>

            <div class="flex flex-col gap-2 pb-3 overflow-y-auto">
                        {{-- <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}" wire:navigate>
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </x-nav-link> --}}
                        @role('user')
                        <x-nav-link :active="request()->routeIs('our.plans')" href="{{ route('our.plans') }}" wire:navigate>
                            <span>Plans</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.my-subscription')" href="{{ route('user.my-subscription') }}">
                            <span>My Subscription</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.my-transactions')" href="{{ route('user.my-transactions') }}"
                            wire:navigate>
                            <span>My Transactions</span>
                        </x-nav-link>
                        @endrole
                        @role('admin')
                        <x-nav-link :active="request()->routeIs('admin.users')" href="{{ route('admin.users') }}" wire:navigate>
                            <span>Users</span>
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('admin.plans')" href="{{ route('admin.plans') }}" wire:navigate>
                            <span>Plans</span>
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('admin.payment.paypal')" href="{{ route('admin.payment.paypal') }}"
                            wire:navigate>
                            <span>Payment Settings</span>
                        </x-nav-link>
                        @endrole

                        {{-- <x-nav-link :active="request()->routeIs('play-ground')" href="{{ route('play-ground') }}" wire:navigate>
                            <i class="fa-solid fa-play fa-spin"></i>
                            <span>To Do</span>
                        </x-nav-link> --}}
            </div>

            {{-- @persist('sidebar')
            <div x-data="{ isExpanded: false }" class="flex flex-col">
                <button type="button" x-on:click="isExpanded = ! isExpanded"
                    class="flex items-center justify-between rounded-md gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2 focus:outline-none focus-visible:underline"
                    x-bind:class="isExpanded ? 'text-neutral-900 bg-black/10 dark:text-white dark:bg-white/10' :  'text-neutral-600 hover:bg-black/5 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/5'">
                    <i class="fa-solid fa-user"></i>
                    <span class="mr-auto text-left">Productivity</span>
                    <i class="transition-transform fa-solid fa-angle-up"
                        x-bind:class="isExpanded ? 'rotate-0' : 'rotate-180'" aria-hidden="true"></i>
                </button>

                <ul x-cloak x-collapse x-show="isExpanded">
                    <li class="px-1 py-0.5 first:mt-2">
                        <x-nav-link href="{{ route('play-ground') }}" wire:navigate>
                            <i class="fa-solid fa-play fa-spin"></i>
                            <span>To Do</span>
                        </x-nav-link>
                    </li>
                </ul>
            </div>
            @endpersist('sidebar') --}}
        </nav>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 min-h-screen bg-white dark:bg-neutral-950">
            <!-- Top navigation -->
            <nav
                class="sticky top-0 z-10 flex items-center py-1 border-b justify-evenly border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="container flex items-center justify-between mx-3 h-14">
                    <!-- Site name + Logo -->
                    <div class="flex items-center gap-4">
                        <a href="{{ route('welcome') }}" class="w-12 text-neutral-600 dark:text-neutral-300"
                            wire:navigate>
                            <x-application-logo />
                        </a>
                    </div>

                    <!-- Desktop navigation -->
                    <div class="items-center hidden gap-4 md:flex ">

                        {{-- <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}"
                            wire:navigate>
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </x-nav-link> --}}
                        @role('user')
                        <x-nav-link :active="request()->routeIs('our.plans')" href="{{ route('our.plans') }}" wire:navigate>
                            <span>Plans</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.my-subscription')" href="{{ route('user.my-subscription') }}"  >
                            <span>My Subscription</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.my-transactions')" href="{{ route('user.my-transactions') }}" wire:navigate>
                                <span>My Transactions</span>
                        </x-nav-link>
                        @endrole
                        @role('admin')
                        <x-nav-link :active="request()->routeIs('admin.users')" href="{{ route('admin.users') }}" wire:navigate>
                            <span>Users</span>
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('admin.plans')" href="{{ route('admin.plans') }}" wire:navigate>
                            <span>Plans</span>
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('admin.payment.paypal')" href="{{ route('admin.payment.paypal') }}" wire:navigate>
                            <span>Payment Settings</span>
                        </x-nav-link>
                        @endrole

                        
                        {{-- <x-nav-link :active="request()->routeIs('play-ground')" href="{{ route('play-ground') }}"
                            wire:navigate>
                            <i class="fa-solid fa-play fa-spin"></i>
                            <span>To Do</span>
                        </x-nav-link> --}}

                    </div>

                    <!-- Right section -->
                    <div class="flex items-center gap-2">

                        <!-- Mobile menu button  -->
                        <button x-on:click="sidebarIsOpen = true"
                            class="md:hidden text-neutral-600 dark:text-neutral-300">
                            <i class="fas fa-bars"></i>
                            <span class="sr-only">Open sidebar</span>
                        </button>

                        <x-theme-toggle />

                        <!-- Profile dropdown -->
                        <div x-data="{ userDropdownIsOpen: false }" class="relative">
                            @auth
                            <!-- Authenticated user profile -->
                            <button @click="userDropdownIsOpen = !userDropdownIsOpen"
                                class="">
                                <livewire:components.auth.user-profile-display />
                            </button>
                            @endauth

                            @guest
                            <!-- Guest user options -->
                            <button @click="userDropdownIsOpen = !userDropdownIsOpen"
                                class="flex items-center gap-2 p-2 rounded-md hover:bg-black/5 dark:hover:bg-white/5 dark:text-white">
                                <i class="fa-solid fa-user"></i>
                            </button>
                            @endguest

                            <!-- Dropdown menu -->
                            <div x-cloak x-show="userDropdownIsOpen" @click.outside="userDropdownIsOpen = false"
                                class="absolute right-0 z-20 w-48 mt-2 bg-white border rounded-md shadow-lg dark:bg-neutral-900 dark:border-neutral-700">
                                <div class="py-1">
                                    @auth
                                    <a href="{{ route('profile') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-neutral-800">
                                        <i class="mr-2 fa-regular fa-user"></i>
                                        Profile
                                    </a>
                                    <livewire:pages.auth.logout />
                                    @endauth

                                    @guest
                                    <a href="{{ route('login') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-neutral-800">
                                        <i class="mr-2 fa-solid fa-right-to-bracket"></i>
                                        Login
                                    </a>
                                    <a href="{{ route('register') }}" wire:navigate
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-neutral-800">
                                        <i class="mr-2 fa-solid fa-plus"></i>
                                        Register
                                    </a>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="container flex-1 p-4 mx-auto">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer
                class="py-3 text-center border-t border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300">
                GeMailAPP Co.Ltd. &copy; 2025<br> All rights reserved.
            </footer>
        </div>
    </div>

    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />
    @if (session('success'))
        <script>
            Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '{{ session('success') }}',
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 3000,
                        // timerProgressBar: true,
                    });
        </script>
    @endif
</body>

</html>
