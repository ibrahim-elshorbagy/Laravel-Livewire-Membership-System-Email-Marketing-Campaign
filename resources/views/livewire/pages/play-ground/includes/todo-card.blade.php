<div x-data="{ todoID: '{{ $todo->id }}' }" wire:key="{{ $todo->id }}"
    class="m-5 col-span-1 p-6 bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border-l-4 {{ $todo->complated ? 'border-l-green-500' : 'border-l-orange-500' }}"
    style="border-left-color: {{ $todo->complated ? 'rgb(34 197 94)' : 'rgb(249 115 22)' }}">

    <div class="flex items-start justify-between gap-4">
        <!-- Left Section: Checkbox and Title -->
        <div class="flex items-center flex-1 gap-4">
            <!-- Checkbox to toggle completion -->
            <div class="flex-shrink-0">
                @if($todo->complated)
                <x-check-box wire:click="toggle({{ $todo->id }})" :checked="true" class="text-green-500" />
                @else
                <x-check-box wire:click="toggle({{ $todo->id }})" class="text-orange-500" />
                @endif
            </div>

            <!-- Title Display or Edit Input -->
            <div class="flex-grow">
                <template x-if="todoID == $wire.editingTodoId">
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="upd_title" class="sr-only">Title</x-input-label>
                            <x-text-input wire:model="upd_title" type="text" placeholder="Update Title"
                                class="w-full border-2 border-orange-300 focus:ring-2 focus:ring-orange-500" />
                            @error('upd_title')
                            <x-input-error :messages="$errors->get('upd_title')" class="mt-1" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="upd_description" class="sr-only">Description</x-input-label>
                            <x-textarea-input wire:model='upd_description' placeholder="Update Description"
                                class="w-full h-24 border-2 border-orange-300 focus:ring-2 focus:ring-orange-500" />
                            @error('upd_description')
                            <x-input-error :messages="$errors->get('upd_description')" />
                            @enderror
                        </div>
                    </div>
                </template>

                <template x-if="todoID !== $wire.editingTodoId">
                    <div>
                        <h3
                            class="text-lg font-bold text-neutral-800 dark:text-white {{ $todo->complated ? 'line-through text-neutral-500' : '' }}">
                            {{ $todo->title }}
                        </h3>
                        <p
                            class="text-neutral-600 dark:text-neutral-300 {{ $todo->complated ? 'line-through text-neutral-400' : '' }}">
                            {{ $todo->description }}
                        </p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Right Section: Action Buttons -->
        <div class="flex items-center gap-2">
            <template x-if="todoID == $wire.editingTodoId">
                <div class="flex items-center gap-2">
                    <!-- Update Button -->
                    <button wire:click="update({{ $todo->id }})"
                        class="px-3 py-2 text-sm font-medium text-white transition-colors bg-green-500 rounded-md hover:bg-green-600">
                        Save
                    </button>

                    <!-- Cancel Button -->
                    <button x-on:click="$wire.editingTodoId = '0'"
                        class="px-3 py-2 text-sm font-medium text-white transition-colors bg-red-500 rounded-md hover:bg-red-600">
                        Cancel
                    </button>
                </div>
            </template>

            <template x-if="todoID !== $wire.editingTodoId">
                <div class="flex items-center gap-2">
                    <!-- Edit Button -->
                    <button x-on:click="
                            $wire.editingTodoId = '{{ $todo->id }}';
                            $wire.upd_title = '{{ $todo->title }}';
                            $wire.upd_description = '{{ $todo->description }}';
                        " class="transition-colors text-neutral-500 hover:text-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>

                    <!-- Delete Button -->
                    <button wire:click="delete({{ $todo->id }})"
                        class="text-red-500 transition-colors hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Created At -->
    <div class="flex items-center justify-between mt-4">
        <span class="text-xs text-neutral-500 dark:text-neutral-400">
            Created: {{ $todo->created_at->format('M d, Y') }}
        </span>
        @if($todo->complated)
        <span class="text-xs font-medium text-green-500">
            Completed
        </span>
        @endif
    </div>
</div>
