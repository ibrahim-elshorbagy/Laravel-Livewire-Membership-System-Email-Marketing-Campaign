<x-app-layout>
    <div
        class="flex flex-col justify-center items-center p-6 min-h-screen rounded-md border group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div class="text-center">
            <i class="mb-8 animate-pulse fa-solid fa-circle-exclamation fa-4x"></i>
        </div>

        <div class="text-center">
            <h1 class="mb-4 text-6xl font-bold">404</h1>
            <p class="mb-8 text-xl text-pretty">
                Page Not Found
            </p>
        </div>

        <div class="flex gap-4">
            <a href="{{ url('/') }}"
                class="flex items-center px-6 py-3 rounded-md border transition-colors hover:bg-neutral-200 dark:hover:bg-neutral-800">
                <i class="mr-2 fa-solid fa-house"></i>
                Go Home
            </a>
        </div>

        <div class="mt-12">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Lost? Try using the navigation menu to find what you're looking for.
            </p>
        </div>

    </div>
</x-app-layout>
