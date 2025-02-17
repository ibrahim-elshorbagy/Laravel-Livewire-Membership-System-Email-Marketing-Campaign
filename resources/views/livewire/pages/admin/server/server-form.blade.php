<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $server_id ? 'Edit Server' : 'New Server' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('admin.servers') }}" wire:navigate
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white transition duration-150 ease-in-out border rounded-md cursor-pointer md:px-4 md:py-2 text-nowrap bg-sky-600 dark:bg-sky-900 group border-sky-300 dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Back To Servers
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="saveServer" class="space-y-4">
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Server Name -->
            <div>
                <x-input-label for="name" required>Server Name</x-input-label>
                <x-text-input wire:model="name" id="name" type="text" class="block w-full mt-1" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Current Quota -->
            <div>
                <x-input-label for="current_quota" required>Current Quota</x-input-label>
                <x-text-input wire:model="current_quota" id="current_quota" type="number" class="block w-full mt-1"
                    required />
                <x-input-error :messages="$errors->get('current_quota')" class="mt-2" />
            </div>

            <!-- Assigned User -->
            <div x-data="{ open: false }" class="relative">
                <x-input-label for="assigned_user">Assigned User</x-input-label>
                <div class="mt-1">
                    <button type="button" @click="open = !open"
                        class="w-full px-4 py-2 text-left border rounded-md shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        @if($assigned_to_user_id && $users->firstWhere('id', $assigned_to_user_id))
                            {{ $users->firstWhere('id', $assigned_to_user_id)->first_name }}
                            {{ $users->firstWhere('id', $assigned_to_user_id)->last_name }}
                        @else
                            Select User
                        @endif
                    </button>

                    <div x-show="open" @click.outside="open = false"
                        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-neutral-800">
                        <div class="p-2">
                            <input type="text" wire:model.live="userSearch"
                                class="w-full px-3 py-2 border rounded-md dark:bg-neutral-700 dark:border-neutral-600"
                                placeholder="Search users...">

                            <div class="mt-2 overflow-y-auto max-h-48">
                                <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700"
                                    wire:click="$set('assigned_to_user_id', null); open = false">
                                    No User (Clear Selection)
                                </div>
                                @foreach($users as $user)
                                <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700
                                    {{ $assigned_to_user_id == $user->id ? 'bg-sky-50 dark:bg-sky-900' : '' }}"
                                    wire:click="$set('assigned_to_user_id', {{ $user->id }}); open = false">
                                    <div class="flex items-center gap-2 w-max">
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

            <div>
                <x-input-label for="last_access_time" :value="__('Last Access Time')" />
                <x-text-input x-data x-init="flatpickr($el, {
                                        dateFormat: 'Y-m-d',
                                        defaultDate: '{{ $last_access_time }}',
                                        allowInput: true
                                    })" wire:model="last_access_time" type="text" class="block w-full mt-1" placeholder="YYYY-MM-DD" />
                <x-input-error :messages="$errors->get('last_access_time')" class="mt-2" />
             </div>
            <!-- Admin Notes -->
            <div class="lg:col-span-2">
                <x-input-label for="admin_notes">Admin Notes</x-input-label>
                <x-primary-textarea wire:model="admin_notes" id="admin_notes" rows="4" class="block w-full mt-1">
                </x-primary-textarea>
                <x-input-error :messages="$errors->get('admin_notes')" class="mt-2" />
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <x-secondary-button type="button" wire:navigate href="{{ route('admin.servers') }}">
                Cancel
            </x-secondary-button>
            <x-primary-create-button type="submit">
                {{ $server_id ? 'Update Server' : 'Create Server' }}
            </x-primary-create-button>
        </div>
    </form>
</div>