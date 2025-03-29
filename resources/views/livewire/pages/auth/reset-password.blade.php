<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
new #[Layout('layouts.app',['title' => 'Reset password | Bulk Email Marketing App'])] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('success', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div
    class="grid relative flex-col px-4 md:px-8 min-h-[85vh] sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="hidden relative flex-col p-10 h-full text-white bg-muted lg:flex dark:border-r dark:border-neutral-800">
        <div class="absolute inset-0 bg-[#f7f7f7]"></div>
        <div class="absolute inset-0 bg-center bg-no-repeat bg-contain"
            style="background-image: url('{{ App\Models\Admin\Site\SiteSetting::getAuthImage() }}');""></div>

    </div>
    <div class="flex justify-center items-center w-full h-full">
        <div class="mx-auto flex w-full flex-col justify-evenly space-y-6 sm:w-[350px] h-full">
            <!-- Logo/Brand -->
            <a href="{{ route('main-site') }}" class="flex flex-col gap-2 items-center font-medium z-5 lg:hidden" wire:navigate>
                <span class="flex justify-center items-center w-[200px]   rounded-md">
                    <x-application-logo class="text-black fill-current size-9 dark:text-white" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white">Reset Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter your new password below</p>
            </div>

            <form wire:submit="resetPassword" class="space-y-6">
                <div class="space-y-4">
                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-sm font-medium" />
                        <x-text-input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-sm font-medium" />
                        <x-text-input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-medium" />
                        <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div>
                        <button type="submit" class="flex justify-center px-4 py-2 w-full text-sm font-medium text-white bg-blue-600 rounded-lg border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset Password
                        </button>
                    </div>

                    <div class="text-sm text-center">
                        <span class="text-neutral-600 dark:text-neutral-400">Remember your password? </span>
                        <a class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300" href="{{ route('login') }}" wire:navigate>
                            Sign in here
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
