<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto space-y-6">
            <div class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <div class="max-w-xl">
                    <livewire:pages.profile.update-profile-information-form />
                </div>
            </div>

            <div class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <div class="max-w-xl">
                    <livewire:pages.profile.update-password-form />
                </div>
            </div>

            <div class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <div class="max-w-xl">
                    <livewire:pages.profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
