<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $server_id ? 'Edit Server' : 'New Server' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('admin.servers') }}" wire:navigate>
                Back To Servers
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="saveServer" class="space-y-4">
        <div class="grid gap-6 lg:grid-cols-2">
            @if($server_id)
            <!-- Single Server Name (Edit Mode) -->
            <div>
                <x-input-label for="name" required>Server Name</x-input-label>
                <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            @else
            <!-- Multiple Server Names (Create Mode) -->
            <div class="lg:col-span-2">
                <x-input-label for="servers" required>Server Names</x-input-label>
                <x-textarea-input wire:model="servers" id="servers" placeholder="Enter server names separated by commas or new lines" class="block mt-1 w-full" required />
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Enter server names using only English letters, dots, and hyphens (server-one.com, server-two)
                </div>
                <x-input-error :messages="$errors->get('servers.*')" class="mt-2" />
                <x-input-error :messages="$errors->get('servers')" class="mt-2" />
            </div>
            @endif

            @if($server_id)
            <!-- Assigned User (Edit Mode Only) -->
            <div x-data="{ open: false }" class="relative">
                <x-input-label for="assigned_user">Assigned User</x-input-label>
                <div class="mt-1">
                    <button type="button" @click="open = !open"
                        class="px-4 py-2 w-full text-left rounded-md border shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        @if($assigned_to_user_id && $users->firstWhere('id', $assigned_to_user_id))
                            {{ $users->firstWhere('id', $assigned_to_user_id)->first_name }}
                            {{ $users->firstWhere('id', $assigned_to_user_id)->last_name }}
                        @else
                            Select User
                        @endif
                    </button>

                    <div x-show="open" @click.outside="open = false"
                        class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg dark:bg-neutral-800">
                        <div class="p-2">
                            <input type="text" wire:model.live="userSearch"
                                class="px-3 py-2 w-full rounded-md border dark:bg-neutral-700 dark:border-neutral-600"
                                placeholder="Search users...">

                            <div class="overflow-y-auto mt-2 max-h-48">
                                <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700"
                                    wire:click="$set('assigned_to_user_id', null); open = false">
                                    No User (Clear Selection)
                                </div>
                                @foreach($users as $user)
                                <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700
                                    {{ $assigned_to_user_id == $user->id ? 'bg-sky-50 dark:bg-sky-900' : '' }}"
                                    wire:click="$set('assigned_to_user_id', {{ $user->id }}); open = false">
                                    <div class="flex gap-2 items-center w-max">
                                        <img class="object-cover rounded-full size-10" src="{{ $user->image_url ?? asset('default-avatar.png') }}"
                                            alt="{{ $user->first_name }} {{ $user->last_name }}" />
                                        <div class="flex flex-col">
                                            <span class="text-neutral-900 dark:text-neutral-100">
                                                {{ $user->first_name }} {{ $user->last_name }}
                                                - ({{ $user->username }})
                                            </span>
                                            <span class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                                {{ $user->email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('assigned_to_user_id')" class="mt-2" />
            </div>

            <!-- Admin Notes (Edit Mode Only) -->
            <div class="lg:col-span-2">
                <x-input-label for="admin_notes">Admin Notes</x-input-label>
                <x-textarea-input wire:model="admin_notes" id="admin_notes" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('admin_notes')" class="mt-2" />
            </div>
            @endif
        </div>

        <div class="flex justify-end mt-6">
            <x-primary-create-button type="submit">
                {{ $server_id ? 'Update Server' : 'Create Servers' }}
            </x-primary-create-button>
        </div>
    </form>
</div>
