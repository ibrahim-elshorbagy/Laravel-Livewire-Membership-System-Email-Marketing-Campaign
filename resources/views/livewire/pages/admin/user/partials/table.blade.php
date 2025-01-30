@props([
'items',
'search',
'searchPlaceholder' => 'Search...',
'showCreateButton' => true,
'isTrashed' => false
])

<div>
    <!-- Search Box -->
    <div class="flex items-center justify-between mb-4">
        <div class="mb-4">
            <div class="relative">
                <x-text-input wire:model.live.debounce.600ms="{{ $search }}" id="search" name="search" type="text"
                    class="w-full py-2 pl-10 pr-20"
                    placeholder="{{ $searchPlaceholder }}" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
                @if($search)
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button wire:click="$set('{{ $search }}', '')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @if($showCreateButton)
        <div class="ml-4">
            <x-primary-create-button href="{{ route('admin.users.create') }}" wire:navigate>
                Create New User
            </x-primary-create-button>
        </div>
        @endif
    </div>

    <!-- Table -->
    <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">User</th>
                    <th scope="col" class="p-4">Company</th>
                    <th scope="col" class="p-4">Country</th>
                    <th scope="col" class="p-4">WhatsApp</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($items as $user)
                <tr>
                    <td class="p-4">
                        <div class="flex items-center gap-2 w-max">
                            <img class="object-cover rounded-full size-10"
                                src="{{ $user->image_url ?? 'default-avatar.png' }}" alt="{{ $user->full_name }}" />
                            <div class="flex flex-col">
                                <span class="text-neutral-900 dark:text-neutral-100">
                                    {{ $user->first_name }} {{ $user->last_name }} - ( {{ $user->username }} )
                                </span>
                                <span class="text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                                    {{ $user->email }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">{{ $user->company }}</td>
                    <td class="p-4">{{ $user->country }}</td>
                    <td class="p-4">
                        <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank"
                            class="text-green-500 hover:underline">
                            {{ $user->whatsapp }}
                        </a>
                    </td>
                    <td class="p-4">
                        <span
                            class="inline-flex overflow-hidden rounded-lg px-1 py-0.5 text-xs font-medium {{ $user->active ? 'text-green-300 bg-green-300/10' : 'text-red-500 bg-red-500/10' }}">
                            {{ $user->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex space-x-2">
                            @if($isTrashed)
                            <x-primary-create-button wire:click="restoreUser({{ $user->id }})"
                                onclick="confirm('Are you sure you want to restore this user?') || event.stopImmediatePropagation()">
                                Restore
                            </x-primary-create-button>

                            <x-primary-danger-button wire:click="forceDeleteUser({{ $user->id }})"
                                onclick="confirm('Are you sure you want to permanently delete this user? This action cannot be undone.') || event.stopImmediatePropagation()">
                                Force Delete
                            </x-primary-danger-button>
                            @else
                            <x-primary-info-button href="{{ route('admin.users.edit', $user) }}" wire:navigate>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-primary-info-button>

                            <x-primary-info-button
                                onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $user->id }})">
                                Login As
                            </x-primary-info-button>

                            @if($user->active)
                            <x-primary-danger-button wire:click="toggleActive({{ $user->id }})">
                                Block
                            </x-primary-danger-button>
                            @else
                            <x-primary-create-button wire:click="toggleActive({{ $user->id }})">
                                Unblock
                            </x-primary-create-button>
                            @endif

                            <x-primary-danger-button wire:click="deleteUser({{ $user->id }})"
                                onclick="confirm('Are you sure you want to delete this user?') || event.stopImmediatePropagation()">
                                <i class="fa-solid fa-trash"></i>
                            </x-primary-danger-button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
