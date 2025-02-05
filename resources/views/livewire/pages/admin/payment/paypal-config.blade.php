<div
    class="flex flex-col p-6 border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
            <i class="text-blue-500 fa-brands fa-paypal fa-2xl"></i>
            <h2 class="text-xl font-semibold">PayPal Configuration</h2>
        </div>
        <div class="flex items-center space-x-2">
            <span
                class="px-3 py-1 text-sm {{ $mode === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-full">
                {{ ucfirst($mode) }} Mode
            </span>
        </div>
    </div>

    <!-- Configuration Form -->
    <form wire:submit.prevent="updatePaypalConfig" class="space-y-4">
        <!-- Mode Selection -->
        <div class="p-4 border rounded-md border-neutral-200 dark:border-neutral-700">
            <label class="block mb-2 text-sm font-medium">Environment</label>
            <div class="flex space-x-4">
                <label class="flex items-center">
                    <input type="radio" wire:model="mode" value="sandbox" class="mr-2">
                    Sandbox
                </label>
                <label class="flex items-center">
                    <input type="radio" wire:model="mode" value="live" class="mr-2">
                    Live
                </label>
            </div>
        </div>

        <!-- API Credentials -->
        <div class="p-4 border rounded-md border-neutral-200 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">API Credentials</h3>
            <div class="space-y-3">
                <div>
                    <x-input-label for="client_id" :value="__('Client ID')" />
                    <x-text-input wire:model="client_id" id="client_id" type="text" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="client_secret" :value="__('Client Secret')" />
                    <x-text-input wire:model="client_secret" id="client_secret" type="text"
                        class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('client_secret')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="app_id" :value="__('App ID')" />
                    <x-text-input wire:model="app_id" id="app_id" type="text" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('app_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="webhook_id" :value="__('Webhook ID')" />
                    <x-text-input wire:model="webhook_id" id="webhook_id" type="text" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('webhook_id')" class="mt-2" />
                </div>
            </div>
        </div>


        <!-- Submit Button -->
        <div class="flex justify-end">
            <x-primary-create-button type="submit">
                Update Configuration
            </x-primary-create-button>
        </div>
    </form>
</div>
