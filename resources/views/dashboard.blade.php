<x-app-layout>
    <div
        class="overflow-hidden relative mb-8 bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
        <div class="absolute inset-0 bg-gradient-to-br to-transparent from-primary-500/10"></div>
        <div class="relative">
            <div class="flex items-center space-x-4">

                <div class="p-4 bg-primary-100 dark:bg-primary-500/10">
                    <div class="flex gap-2 items-center rounded-md">
                        <img src="{{ Auth::user()->image_url }}" class="object-cover rounded-md size-24" alt="avatar">
                    </div>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                        Welcome back, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}!
                    </h1>
                    <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        {{ now()->format('l, j F Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @role('admin')
    <div class="space-y-6">
        <div wire:key="statics-container" class="w-full">
            <livewire:pages.admin.dashboard.dashboard-statics />
        </div>

        <div class="mt-5" wire:key="api-requests-container" class="w-full">
            <livewire:pages.admin.dashboard.dashboard-api-requests />
        </div>
    </div>
    @endrole

    @role('user')
    <div class="space-y-6">
        <div wire:key="statics-container" class="w-full">
            <livewire:pages.user.dashboard.dashboard-statics />
        </div>
    </div>
    @endrole
</x-app-layout>
