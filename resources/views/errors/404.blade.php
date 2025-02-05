<x-app-layout>
    <div
        class="flex flex-col items-center justify-center min-h-screen p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div class="text-center">
            <i class="mb-8 fa-solid fa-circle-exclamation fa-4x animate-pulse"></i>
        </div>

        <div class="text-center">
            <h1 class="mb-4 text-6xl font-bold">404</h1>
            <p class="mb-8 text-xl text-pretty">
                Oops! The page you're looking for seems to have wandered off.
            </p>
        </div>

        <div class="flex gap-4">
            <a href="{{ url('/') }}"
                class="flex items-center px-6 py-3 transition-colors border rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-800">
                <i class="mr-2 fa-solid fa-house"></i>
                Go Home
            </a>

            <a href="{{ url()->previous() }}"
                class="flex items-center px-6 py-3 transition-colors border rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-800">
                <i class="mr-2 fa-solid fa-arrow-left"></i>
                Go Back
            </a>
        </div>

        <div class="mt-12">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Lost? Try using the navigation menu to find what you're looking for.
            </p>
        </div>

    </div>
</x-app-layout>
