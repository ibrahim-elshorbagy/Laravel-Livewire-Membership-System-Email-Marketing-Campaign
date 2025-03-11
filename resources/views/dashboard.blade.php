<x-app-layout>

    <livewire:pages.admin.dashboard.welcome-section />

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
