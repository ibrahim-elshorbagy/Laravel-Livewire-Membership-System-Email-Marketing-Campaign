<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        // Validate the user's current password before proceeding
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user exists before attempting deletion
        if ($user) {
            // Delete the user and log them out
            $user->delete();
            Auth::logout();

            // Redirect to the homepage with navigation
            $this->redirect('/', navigate: true);
        } else {
            // Handle case where no authenticated user is found
            abort(403, 'Unauthorized action. No user to delete.');
        }
    }
}; ?>

<section class="space-y-6">
    <header class="flex items-center gap-5">

        <i class="fa-solid fa-trash fa-2xl"></i>
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Delete Account') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your
                account, please download any data or information that you wish to retain.') }}
            </p>
        </div>

    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="block w-3/4 mt-1"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
