<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="initThemeHandler()" x-init="init()"
    x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ $globalSettings['favicon'] }}" rel="icon">
    <title>{{ config('app.name', 'LivewireSaaS') }}{{ isset($title) ? " - $title" : '' }}</title>
    <meta name="description" content="{{ $globalSettings['meta_description'] }}">
    <meta name="keywords" content="{{ $globalSettings['meta_keywords'] }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('styles')
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <meta itemprop="image" content="{{ $globalSettings['logo'] }}" />
    <meta property="og:image" content="{{ $globalSettings['logo'] }}" />
    <meta name="twitter:image" content="{{ $globalSettings['logo'] }}" />
    @stack('seo')
</head>

<body class="font-sans antialiased">

    <div x-data="{ sidebarIsOpen: false }">
        <!-- Mobile sidebar overlay -->
        <div x-cloak x-show="sidebarIsOpen" class="fixed inset-0 z-20 backdrop-blur-sm bg-neutral-950/10 lg:hidden"
            aria-hidden="true" x-on:click="sidebarIsOpen = false" x-transition.opacity></div>

        <!-- Mobile sidebar -->
        <nav x-cloak
            class="fixed left-0 z-30 flex flex-col p-4 transition-transform duration-300 border-r w-60 h-svh shrink-0 border-neutral-300 bg-neutral-50 lg:hidden lg:w-64 lg:translate-x-0 dark:border-neutral-700 dark:bg-neutral-900"
            x-bind:class="sidebarIsOpen ? 'translate-x-0' : '-translate-x-60'">
            <a href="{{ route('main-site') }}"
                class="w-24 mb-4 ml-2 text-2xl font-bold text-neutral-900 dark:text-white">
                <x-application-logo />
            </a>


            <div class="flex flex-col gap-2 pb-3">
                @auth
                <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}" wire:navigate>
                    <span>Dashboard</span>
                </x-nav-link>
                @endauth
                @role('user')
                <x-nav-link :active="request()->routeIs('user.my-subscription')"
                    href="{{ route('user.my-subscription') }}">
                    <span>Subscription</span>
                </x-nav-link>
                <x-nav-link :active="request()->routeIs('user.my-transactions')"
                    href="{{ route('user.my-transactions') }}" wire:navigate>
                    <span>Transactions</span>
                </x-nav-link>
                <x-nav-link :active="request()->routeIs('user-settings')" href="{{ route('user-settings') }}" wire:navigate>
                    <span>Settings</span>
                </x-nav-link>
                @endrole




                @role('admin')
                <x-nav-link :active="request()->routeIs('admin.users') ||
                            request()->routeIs('admin.users.create') ||
                            request()->routeIs('admin.users.edit')" href="{{ route('admin.users') }}" wire:navigate>
                    <span>Users</span>
                </x-nav-link>
                <x-nav-link :active="request()->routeIs('admin.subscriptions')"
                    href="{{ route('admin.subscriptions') }}" wire:navigate>
                    <span>Subscriptions</span>
                </x-nav-link>
                <x-nav-link
                    :active="request()->routeIs('admin.payment.transactions') || request()->routeIs('admin.users.transactions')"
                    href="{{ route('admin.payment.transactions') }}" wire:navigate>
                    <span>Transactions</span>
                </x-nav-link>
                <x-nav-link :active="request()->routeIs('admin.plans')" href="{{ route('admin.plans') }}" wire:navigate>
                    <span>Plans</span>
                </x-nav-link>
                <x-nav-link :active="request()->routeIs('admin.support.tickets')" href="{{ route('admin.support.tickets') }}" wire:navigate>
                    <span>Support Tickets</span>
                </x-nav-link>
                @endrole

            </div>

            @role('user')
            @persist('sidebar')
            <div x-data="{ isCampaignExpanded: false}" class="flex flex-col space-y-2">
                <button type="button" x-on:click="isCampaignExpanded = ! isCampaignExpanded"
                    class="flex gap-2 justify-between items-center px-2 py-1.5 text-sm font-medium rounded-md underline-offset-2 focus:outline-none focus-visible:underline"
                    x-bind:class="isCampaignExpanded ? 'text-neutral-900 bg-black/10 dark:text-white dark:bg-white/10' :  'text-neutral-600 hover:bg-black/5 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/5'">
                    <span class="mr-auto text-left">Campaigns</span>
                    <i class="transition-transform fa-solid fa-angle-up"
                        x-bind:class="isCampaignExpanded ? 'rotate-0' : 'rotate-180'" aria-hidden="true"></i>
                </button>

                <ul x-cloak x-collapse x-show="isCampaignExpanded">
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('user.campaigns.list')" href="{{ route('user.campaigns.list') }}" wire:navigate>
                            <span>Campaigns</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.emails.index')" href="{{ route('user.emails.index') }}" wire:navigate>
                            <span>Mailing list</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.email-messages')" href="{{ route('user.email-messages') }}" wire:navigate>
                            <span>Messages</span>
                        </x-nav-link>
                    </li>
                </ul>
            </div>
            <div x-data="{ isSupportExpanded: false}" class="flex flex-col space-y-2">
                <button type="button" x-on:click="isSupportExpanded = ! isSupportExpanded"
                    class="flex gap-2 justify-between items-center px-2 py-1.5 text-sm font-medium rounded-md underline-offset-2 focus:outline-none focus-visible:underline"
                    x-bind:class="isSupportExpanded ? 'text-neutral-900 bg-black/10 dark:text-white dark:bg-white/10' :  'text-neutral-600 hover:bg-black/5 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/5'">
                    <span class="mr-auto text-left">Support</span>
                    <i class="transition-transform fa-solid fa-angle-up"
                        x-bind:class="isSupportExpanded ? 'rotate-0' : 'rotate-180'" aria-hidden="true"></i>
                </button>

                <ul x-cloak x-collapse x-show="isSupportExpanded">
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('user.support.tickets')" href="{{ route('user.support.tickets') }}"
                            wire:navigate>
                            <span>My Tickets</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.support.create')" href="{{ route('user.support.create') }}"
                            wire:navigate>
                            <span>Create Ticket</span>
                        </x-nav-link>
                    </li>
                </ul>
            </div>
            @endpersist('sidebar')
            @endrole

            @role('admin')
            @persist('sidebar')
            <div x-data="{ isEmailExpanded: false, isSettingsExpanded: false ,isPaymentExpanded: false}"
                class="flex flex-col space-y-2">
                <!-- Payment Settings Dropdown -->
                <button type="button" x-on:click="isPaymentExpanded = ! isPaymentExpanded"
                    class="flex gap-2 justify-between items-center px-2 py-1.5 text-sm font-medium rounded-md underline-offset-2 focus:outline-none focus-visible:underline"
                    x-bind:class="isPaymentExpanded ? 'text-neutral-900 bg-black/10 dark:text-white dark:bg-white/10' :  'text-neutral-600 hover:bg-black/5 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/5'">
                    <span class="mr-auto text-left">Payment</span>
                    <i class="transition-transform fa-solid fa-angle-up"
                        x-bind:class="isPaymentExpanded ? 'rotate-0' : 'rotate-180'" aria-hidden="true"></i>
                </button>

                <ul x-cloak x-collapse x-show="isPaymentExpanded">
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.payment.paypal')"
                            href="{{ route('admin.payment.paypal') }}" wire:navigate>
                            <span>PayPal settings</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.payment.paypal.responses')"
                            href="{{ route('admin.payment.paypal.responses') }}" wire:navigate>
                            <span>Paypal Responses</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.offline-payment-methods')"
                            href="{{ route('admin.offline-payment-methods') }}" wire:navigate>
                            <span>Offline Payment Methods</span>
                        </x-nav-link>
                    </li>
                </ul>

                <!-- System Settings Dropdown -->
                <button type="button" x-on:click="isSettingsExpanded = ! isSettingsExpanded"
                    class="flex gap-2 justify-between items-center px-2 py-1.5 text-sm font-medium rounded-md underline-offset-2 focus:outline-none focus-visible:underline"
                    x-bind:class="isSettingsExpanded ? 'text-neutral-900 bg-black/10 dark:text-white dark:bg-white/10' :  'text-neutral-600 hover:bg-black/5 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/5'">
                    <span class="mr-auto text-left">Settings</span>
                    <i class="transition-transform fa-solid fa-angle-up"
                        x-bind:class="isSettingsExpanded ? 'rotate-0' : 'rotate-180'" aria-hidden="true"></i>
                </button>

                <ul x-cloak x-collapse x-show="isSettingsExpanded">
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.site-settings')"
                            href="{{ route('admin.site-settings') }}" wire:navigate>
                            <span>Site Settings</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.ai-settings')"
                            href="{{ route('admin.ai-settings') }}" wire:navigate>
                            <span>AI Settings</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.site-prohibited-words')"
                            href="{{ route('admin.site-prohibited-words') }}" wire:navigate>
                            <span>Prohibited Words</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.site-api-requests')"
                            href="{{ route('admin.site-api-requests') }}" wire:navigate>
                            <span>API requests and fails</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.servers')" href="{{ route('admin.servers') }}"
                            wire:navigate>
                            <span>Sending bots</span>
                        </x-nav-link>
                    </li>
                    <li class="px-1 py-0.5">
                        <x-nav-link :active="request()->routeIs('admin.site-system-emails')" href="{{ route('admin.site-system-emails') }}" wire:navigate>
                            <span>Email templates</span>
                        </x-nav-link>
                    </li>
                </ul>
            </div>
            @endpersist('sidebar')
            @endrole


        </nav>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 min-h-screen bg-white dark:bg-neutral-950">
            <!-- Top navigation -->
            <nav
                class="sticky top-0 z-10 flex items-center py-1 border-b justify-evenly border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                <div class="container flex items-center justify-between mx-3 h-14">
                    <!-- Site name + Logo -->
                    <div class="flex items-center gap-4">
                        <a href="{{ route('main-site') }}" class="w-32 text-neutral-600 dark:text-neutral-300">
                            <x-application-logo />
                        </a>
                    </div>

                    <!-- Desktop navigation -->
                    <div class="items-center hidden gap-2 lg:flex">
                        @auth
                        <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}"
                            wire:navigate>
                            <span>Dashboard</span>
                        </x-nav-link>
                        @endauth
                        @role('user')
                        {{-- <x-nav-link :active="request()->routeIs('our.plans')" href="{{ route('our.plans') }}"
                            wire:navigate>
                            <span>Plans</span>
                        </x-nav-link> --}}

                        <x-nav-link :active="request()->routeIs('user.my-subscription')"
                            href="{{ route('user.my-subscription') }}">
                            <span class="text-nowrap">Subscription</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('user.my-transactions')"
                            href="{{ route('user.my-transactions') }}" wire:navigate>
                            <span class="text-nowrap">Transactions</span>
                        </x-nav-link>



                        <x-nav-link :active="request()->routeIs('user-settings')" href="{{ route('user-settings') }}"
                            wire:navigate>
                            <span>Settings</span>
                        </x-nav-link>
                        <x-primary-dropdown label="Campaigns">

                            <x-nav-link :active="request()->routeIs('user.campaigns.list')"
                                href="{{ route('user.campaigns.list') }}" wire:navigate>
                                <span>Campaigns</span>
                            </x-nav-link>

                            <x-nav-link :active="request()->routeIs('user.emails.index')"
                                href="{{ route('user.emails.index') }}" wire:navigate>
                                <span class="text-nowrap">Mailing list</span>
                            </x-nav-link>

                            <x-nav-link :active="request()->routeIs('user.email-messages')"
                                href="{{ route('user.email-messages') }}" wire:navigate>
                                <span class="text-nowrap">Messages</span>
                            </x-nav-link>

                        </x-primary-dropdown>
                        <x-primary-dropdown label="Support">
                            <x-nav-link :active="request()->routeIs('user.support.tickets')"
                                href="{{ route('user.support.tickets') }}" wire:navigate>
                                <span>My Tickets</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('user.support.create')"
                                href="{{ route('user.support.create') }}">
                                <span>Create Ticket</span>
                            </x-nav-link>
                        </x-primary-dropdown>

                        @endrole




                        @role('admin')
                        <x-nav-link :active="request()->routeIs('admin.users') ||
                                    request()->routeIs('admin.users.create') ||
                                    request()->routeIs('admin.users.edit')" href="{{ route('admin.users') }}"
                            wire:navigate>
                            <span>Users</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('admin.subscriptions')"
                            href="{{ route('admin.subscriptions') }}" wire:navigate>
                            <span>Subscriptions</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('admin.payment.transactions') ||
                                    request()->routeIs('admin.users.transactions.*')"
                            href="{{ route('admin.payment.transactions') }}" wire:navigate>
                            <span>Transactions</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('admin.plans')" href="{{ route('admin.plans') }}"
                            wire:navigate>
                            <span>Plans</span>
                        </x-nav-link>
                        <x-nav-link :active="request()->routeIs('admin.support.tickets')" href="{{ route('admin.support.tickets') }}" wire:navigate>
                            <span>Support Tickets</span>
                        </x-nav-link>

                        <!-- Dropdown menus -->
                        <x-primary-dropdown label="Payment">
                            <x-nav-link :active="request()->routeIs('admin.payment.paypal')"
                                href="{{ route('admin.payment.paypal') }}" wire:navigate>
                                <span>PayPal settings</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.payment.paypal.responses')"
                                href="{{ route('admin.payment.paypal.responses') }}" wire:navigate>
                                <span>Paypal Responses</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.offline-payment-methods')"
                                href="{{ route('admin.offline-payment-methods') }}" wire:navigate>
                                <span>Offline Payment Methods</span>
                            </x-nav-link>
                        </x-primary-dropdown>

                        <x-primary-dropdown label="Settings">
                            <x-nav-link :active="request()->routeIs('admin.site-settings')"
                                href="{{ route('admin.site-settings') }}" wire:navigate>
                                <span>Site Settings</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.ai-settings')"
                                href="{{ route('admin.ai-settings') }}" wire:navigate>
                                <span>Ai Settings</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.site-prohibited-words')"
                                href="{{ route('admin.site-prohibited-words') }}" wire:navigate>
                                <span>Prohibited Words</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.site-api-requests')"
                                href="{{ route('admin.site-api-requests') }}" wire:navigate>
                                <span>API requests and fails</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.servers')" href="{{ route('admin.servers') }}" wire:navigate>
                                <span>Sending bots</span>
                            </x-nav-link>
                            <x-nav-link :active="request()->routeIs('admin.site-system-emails')" href="{{ route('admin.site-system-emails') }}" wire:navigate>
                                <span>Email templates</span>
                            </x-nav-link>
                        </x-primary-dropdown>



                        @endrole


                    </div>

                    <!-- Right section -->
                    <div class="flex items-center gap-2">

                        @auth
                        <livewire:megaphone />
                        @endauth
                        <x-theme-toggle />
                        <!-- Profile dropdown -->
                        <div x-data="{ userDropdownIsOpen: false }" class="relative">
                            @auth
                            <!-- Authenticated user profile -->
                            <button @click="userDropdownIsOpen = !userDropdownIsOpen" class="">
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
                                    @if(session()->has('impersonated_by'))
                                    <a href="{{ route('revert.impersonate') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-neutral-800">
                                        <i class="fa-solid fa-backward"></i>
                                        Back to Admin
                                    </a>
                                    @endif
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

                    <!-- Mobile menu button  -->
                    <button x-on:click="sidebarIsOpen = true" class="lg:hidden text-neutral-600 dark:text-neutral-300">
                        <i class="fas fa-bars"></i>
                        <span class="sr-only">Open sidebar</span>
                    </button>
                </div>
            </nav>

            <!-- Main content -->
            <main
                class="flex-1 {{ request()->routeIs('login', 'register','password.request','password.reset','verification.notice','verification.verify','password.confirm') ? '' : 'py-2 px-2 md:mx-4 my-4 ' }}">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer
                class="py-3 mt-auto text-xs text-center border-t md:text-base border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300">

                <div>{{ $globalSettings['footer_first_line']}}</div>
                <div>{{ $globalSettings['footer_second_line'] }}</div>
            </footer>
        </div>
    </div>

    @livewireScripts
    <script data-navigate-once src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    @if (session('welcome-flash'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '<div class="text-2xl font-bold text-primary-600 dark:text-primary-400">Welcome To {{ config('app.name', 'Laravel') }}! 🎉</div>',
            html: '<div class="mt-2 text-base text-neutral-600 dark:text-neutral-400">{{ session('welcome-flash') }}</div>',
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            background: 'bg-white dark:bg-neutral-800',
            customClass: {
                popup: 'p-6 rounded-xl shadow-xl border border-neutral-200 dark:border-neutral-700 max-w-md w-11/12',
                title: 'p-0 mb-1',
                htmlContainer: 'p-0'
            },
            showClass: {
                popup: 'animate-fade-up animate-duration-300'
            },
            hideClass: {
                popup: 'animate-fade-down animate-duration-300'
            }
        });
    </script>
    @endif

    @if (session('info'))
    <script>
        Swal.fire({
                    icon: 'info',
                    title: 'Info!',
                    text: '{{ session('info') }}',
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000,
                    // timerProgressBar: true,
                });
    </script>
    @endif


    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Organization",
            "name": "GeMailAPP-user",
            "logo": "images/default-logo.png"
        }
    </script>
    @stack('scripts')
</body>


</html>
