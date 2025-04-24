@props([
'items',
'search',
'searchPlaceholder' => 'Search...',
'showCreateButton' => true,
'isTrashed' => false
])

<div>
    <!-- Search Box -->
    <div class="flex flex-col gap-2 justify-between items-center mb-4 md:flex-row">
        <div class="mb-4">
            <div class="relative">
                <x-text-input wire:model.live.debounce.600ms="{{ $search }}" id="search" name="search" type="text"
                    class="py-2 pr-20 pl-10 w-full"
                    placeholder="{{ $searchPlaceholder }}" />
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fas fa-search"></i>
                </div>
                @if($search)
                <div class="flex absolute inset-y-0 right-0 items-center pr-3">
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
    <div class="overflow-hidden overflow-x-auto w-full rounded-lg">
        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
            <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                <tr>
                    <th scope="col" class="p-4">User</th>
                    <th scope="col" class="p-4">Company</th>
                    <th scope="col" class="p-4">Country</th>
                    <th scope="col" class="p-4">WhatsApp</th>
                    <th scope="col" class="p-4">Status</th>
                    <th scope="col" class="p-4">Email Verified</th>
                    <th scope="col" class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                @foreach($items as $user)
                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    <td class="p-4">
                        <div class="flex gap-2 items-center w-max">
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
                            class="text-green-500 hover:underline text-nowrap">
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
                        <span
                            class="inline-flex overflow-hidden rounded-lg px-1 py-0.5 text-xs font-medium {{ $user->email_verified_at ? 'text-blue-300 bg-blue-300/10' : 'text-yellow-500 bg-yellow-500/10' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
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
                                onclick="confirm('Are you sure you want to permanently delete this user ? All His Transactions And Subscriptions Will Be Deleted This action cannot be undone.') || event.stopImmediatePropagation()">
                                Force Delete
                            </x-primary-danger-button>
                            @else
                            <x-primary-info-button href="{{ route('admin.users.edit', $user) }}" wire:navigate>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </x-primary-info-button>

                            <x-primary-info-button
                                x-on:click="$dispatch('open-modal', 'send-email-modal');$wire.selectedIdUserToEMail= {{ $user->id }};">
                                Send Email
                            </x-primary-info-button>

                            <x-primary-info-button
                                onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $user->id }})">
                                Login
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

    <!-- Send- Email Modal -->
    <x-modal name="send-email-modal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium">Select Email Template</h2>
            <div class="mt-4">
                <x-input-label for="email_id">Email Template</x-input-label>
                <x-primary-select-input wire:model="selectedEmailId" id="email_id" class="block mt-1 w-full">
                    <option value="">Select an email template</option>
                    @foreach($system_emails as $email)
                    <option value="{{ $email->id }}">{{ $email->name }} - ({{ $email->email_subject }})</option>
                    @endforeach
                </x-primary-select-input>
                <x-input-error :messages="$errors->get('selectedEmailId')" class="mt-2" />
            </div>
            @if ($errors->any())
                <div class="p-4 mt-4 bg-red-50 rounded-md border border-red-200 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="w-5 h-5 text-red-400 fas fa-times-circle"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                There were errors with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <ul class="pl-5 space-y-1 list-disc">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex justify-end mt-6 space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>
                <x-primary-create-button wire:click="goToEmailEditor()" >
                    Continue to Editor
                </x-primary-create-button>
            </div>
        </div>
    </x-modal>
</div>
