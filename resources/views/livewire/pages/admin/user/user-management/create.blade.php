<div>
    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                    Create New User
                </h2>
            </div>
            <div class="flex mt-4 md:mt-0 md:ml-4">
                <x-primary-info-button href="{{ route('admin.users') }}" wire:navigate>
                    Back to Users
                </x-primary-info-button>
            </div>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg shadow bg-neutral-50 dark:bg-neutral-900">
            <form wire:submit.prevent="createUser" class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <!-- Email -->
                    <div class="sm:col-span-2">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model.defer="email" id="email" type="email" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- First Name -->
                    <div>
                        <x-input-label for="first_name" :value="__('First Name')" />
                        <x-text-input type="text" wire:model.defer="first_name" id="first_name"/>
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>

                    <!-- Last Name -->
                    <div>
                        <x-input-label for="last_name" :value="__('Last Name')" />
                        <x-text-input type="text" wire:model.defer="last_name" id="last_name"/>
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>

                    <!-- Username -->
                    <div>
                        <x-input-label for="username" :value="__('Username')" />
                        <x-text-input type="text" wire:model.defer="username" id="username" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <!-- Company -->
                    <div>
                        <x-input-label for="company" :value="__('Company')" />
                        <x-text-input type="text" wire:model.defer="company" id="company"/>
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>

                    <!-- Country -->
                    <div>
                        <x-input-label for="country" :value="__('Country')" />
                        <x-text-input type="text" wire:model.defer="country" id="country"/>
                        <x-input-error :messages="$errors->get('country')" class="mt-2" />
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                        <x-text-input type="text" wire:model.defer="whatsapp" id="whatsapp" placeholder="+01096325697"/>
                        <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input type="password" wire:model.defer="password" id="password"/>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input type="password" wire:model.defer="password_confirmation" id="password_confirmation"/>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <x-primary-select-input wire:model.defer="selectedRole" id="role">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </x-primary-select-input>
                        <x-input-error :messages="$errors->get('selectedRole')" class="mt-2" />
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox" wire:model.defer="active" id="active"
                            class="w-4 h-4 border-neutral-300 dark:border-neutral-700 text-primary-600 focus:ring-primary-500">
                        <x-input-label for="active" :value="__('Active Account')" class="block ml-2 text-sm text-neutral-600 dark:text-neutral-300" />
                    </div>
                    <x-input-error :messages="$errors->get('active')" class="mt-2" />
                </div>

                <!-- Permissions Section -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Permissions <span class="text-xs font-light">(Admin Does Not Need Permissions)</span></h3>
                    <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach($allPermissions as $permission)
                            <x-check-box wire:model.defer="permissions" value="{{ $permission->name }}">
                                {{ $this->formatPermissionName($permission->name) }}
                            </x-check-box>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <x-primary-info-button href="{{ route('admin.users') }}" wire:navigate>
                        Cancel
                    </x-primary-info-button>
                    <x-primary-create-button type="submit">
                        Create User
                    </x-primary-create-button>
                </div>
            </form>
        </div>
    </div>
</div>

