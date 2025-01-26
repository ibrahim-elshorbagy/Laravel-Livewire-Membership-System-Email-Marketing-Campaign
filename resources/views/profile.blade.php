<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Profile Information Section -->
            <div
                class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Personal Information
                </h3>
                <div class="max-w-xl">
                    <livewire:pages.profile.update-profile-information-form />
                </div>
            </div>

            <!-- Profile image Section -->
            <div
                class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Personal Information
                </h3>
                <div class="max-w-xl">
                    <livewire:pages.profile.update-image-form />
                </div>
            </div>

            <!-- Security Section -->
            <div
                class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Security Settings
                </h3>
                <div class="max-w-xl">
                    <livewire:pages.profile.update-password-form />
                </div>
            </div>

            <!-- Account Section -->
            <div
                class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Account Management
                </h3>
                <div class="max-w-xl">
                    <livewire:pages.profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
