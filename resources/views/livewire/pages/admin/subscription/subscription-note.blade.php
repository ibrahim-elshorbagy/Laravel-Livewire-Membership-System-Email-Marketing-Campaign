<div class="p-6">
    <form wire:submit.prevent="updateNote">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            Subscription Note
        </h2>

        <div class="mt-6">
            <x-primary-textarea wire:model.live.debounce.500ms="content" placeholder="Enter note for this subscription..."
                class="w-full h-64">
            </x-primary-textarea>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
            <x-secondary-button x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>

            <x-primary-create-button type="submit">
                Save
            </x-primary-create-button>
        </div>
    </form>
</div>
