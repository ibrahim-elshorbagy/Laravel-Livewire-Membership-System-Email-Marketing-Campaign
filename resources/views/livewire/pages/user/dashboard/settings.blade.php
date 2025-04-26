<x-app-layout>



    <div class="py-6">
        <div class="mx-auto space-y-6 max-w-4xl">

            <div class="flex items-center justify-between p-6 rounded-md border group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Settings
                </h2>

                <x-primary-info-button href="{{ route('user.servers') }}" wire:navigate>
                    My Servers
                </x-primary-info-button>
            </div>
            <div
                class="flex flex-col p-6 rounded-md border group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Unsubscribe link section
                </h3>
                <div>
                    <livewire:pages.profile.update-unsubscribe-link-form />
                </div>
            </div>

            <div
                class="flex flex-col p-6 rounded-md border group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                <h3 class="mb-4 text-lg font-semibold">
                    Email Bounces
                </h3>
                <div>
                    <livewire:pages.profile.update-email-bounces />
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
