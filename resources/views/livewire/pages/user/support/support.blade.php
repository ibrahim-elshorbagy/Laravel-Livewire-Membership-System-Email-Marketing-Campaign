<div class="container p-6 mx-auto">
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
            {{ __('Support') }}
        </h2>

        <form wire:submit.prevent="sendSupportMessage" class="space-y-6">
            <div class="grid gap-6 p-4 border rounded-lg md:grid-cols-1 border-neutral-200 dark:border-neutral-600">
                <!-- User Info (Read-only) -->
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input wire:model="name" id="name" type="text" class="block w-full mt-1" readonly />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" type="email" class="block w-full mt-1" readonly />
                </div>

                <!-- Subject -->
                <div>
                    <x-input-label for="subject" :value="__('Subject')" />
                    <x-text-input wire:model="subject" id="subject" type="text" class="block w-full mt-1" required />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <!-- Message -->
                <div>
                    <x-input-label for="message" :value="__('Message')" />
                    <x-primary-textarea wire:model="message" id="message" rows="9" required></x-primary-textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-create-button type="submit">
                    {{ __('Send Message') }}
                </x-primary-create-button>
            </div>
        </form>
    </div>
</div>
