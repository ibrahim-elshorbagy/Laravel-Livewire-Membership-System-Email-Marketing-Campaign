<div class="max-w-4xl mx-auto overflow-hidden bg-white shadow-2xl dark:bg-neutral-900 rounded-xl">
    {{-- Header Section --}}
    <header class="p-6 text-white bg-gradient-to-r from-orange-500 to-amber-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Create Your To-Do List</h2>
                    <p class="text-sm opacity-80">Organize, prioritize, and conquer your tasks</p>
                </div>
            </div>
        </div>
    </header>

    <div
        class="flex flex-col border rounded-md group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        {{-- Create Todo Form --}}
        <div class="w-1/2 p-6 mx-auto">
            <form class="space-y-6">
                <div class="grid gap-6 mx-auto">
                    {{-- Title Input --}}
                    <div>
                        <x-input-label for="title" class="mb-2 text-orange-600 dark:text-orange-400">
                            Task Title 🔥
                        </x-input-label>
                        <x-text-input wire:model='title' type="text" id="title" placeholder="What's your burning task?"
                            class="w-full transition-all border-2 border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500" />
                        @error('title')
                        <x-input-error :messages="$errors->get('title')" class="mt-2 text-red-500" />
                        @enderror
                    </div>

                    {{-- Description Input --}}
                    <div>
                        <x-input-label for="description" class="mb-2 text-orange-600 dark:text-orange-400">
                            Task Details 🚀
                        </x-input-label>
                        <x-textarea-input wire:model='description' id="description"
                            placeholder="Describe your task in detail..."
                            class="w-full h-32 transition-all border-2 border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500" />
                        @error('description')
                        <x-input-error :messages="$errors->get('description')" class="mt-2 text-red-500" />
                        @enderror
                    </div>
                </div>

                {{-- Submit Section --}}
                <div class="flex items-center justify-center">
                    <x-primary-button wire:click.prevent="create"
                        class="transition-all bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 group-hover:animate-bounce"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Create To-Do
                    </x-primary-button>

                    <x-action-message class="font-semibold text-green-600 animate-pulse" on="submit">
                        Task Ignited! 🔥
                    </x-action-message>
                </div>
            </form>
        </div>

        {{-- Search Section --}}
        <div class="p-6 bg-neutral-100 dark:bg-neutral-700">
            <div class="max-w-md mx-auto">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-neutral-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <x-text-input wire:model.live.debounce.500ms='search' type="text" placeholder="Search tasks..."
                        class="w-full pl-10 rounded-full border-neutral-300 focus:ring-2 focus:ring-orange-500" />
                </div>
            </div>
        </div>

        {{-- Todos List --}}
        <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @forelse ($this->list() as $todo)
            @include('livewire.pages.play-ground.includes.todo-card', ['todo' => $todo])
            @empty
            <div class="p-6 text-center text-neutral-500">
                No tasks found. Create your first to-do!
            </div>
            @endforelse

            {{-- Pagination --}}
            <div class="p-6">
                {{ $this->list()->links() }}
            </div>
        </div>
    </div>
</div>
