<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>


<div
    class="grid relative flex-col h-[85vh] md:px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="hidden relative flex-col p-10 h-full text-white bg-muted lg:flex dark:border-r dark:border-neutral-800">
        <div class="absolute inset-0 bg-[#f7f7f7]"></div>
        <div class="absolute inset-0 bg-center bg-no-repeat bg-contain"
            style="background-image: url({{ asset('assets/auth/auth.jpeg') }});">
        </div>

        <div class="relative z-20 mt-auto text-black my-15">
            <blockquote class="space-y-2">
                <p class="text-lg">Create mailing campaigns and send emails to your subscribers and contacts in an easy
                    and quick way.</p>
            </blockquote>
        </div>
    </div>
    <div class="flex justify-center items-center w-full h-full">
        <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
            <a href="https://gemailapp.com/" class="flex flex-col gap-2 items-center font-medium z-5 lg:hidden"
                wire:navigate>
                <span class="flex justify-center items-center w-[200px] h-48 rounded-md">
                    <x-application-logo class="text-black fill-current size-9 dark:text-white" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <!-- Logo/Brand -->
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white">Welcome back</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Sign in to your account</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <div class="space-y-4 rounded-md shadow-sm">
                    <!-- Email or Username -->
                    <div>
                        <x-input-label for="login" :value="__('Email or Username')" class="text-sm font-medium" />
                        <x-text-input wire:model="form.login" id="login" type="text" name="login" required autofocus
                            autocomplete="username" />
                        <x-input-error :messages="$errors->get('form.login')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-sm font-medium" />
                        <x-text-input wire:model="form.password" id="password" type="password" name="password" required
                            autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input wire:model="form.remember" id="remember" type="checkbox"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700"
                            name="remember">
                        <label for="remember" class="block ml-2 text-xs text-neutral-900 dark:text-neutral-300">
                            Remember me
                        </label>
                    </div>

                    <a class="text-xs font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                        href="{{ route('password.request') }}" wire:navigate>
                        Forgot your password?
                    </a>
                </div>

                <div>
                    <button
                        class="flex justify-center px-4 py-2 w-full text-sm font-medium text-white bg-blue-600 rounded-lg border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Log in
                    </button>
                </div>

                <div class="text-sm text-center">
                    <span class="text-neutral-600 dark:text-neutral-400">Don't have an account? </span>
                    <a class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                        href="{{ route('register') }}" wire:navigate>
                        Sign up here
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>