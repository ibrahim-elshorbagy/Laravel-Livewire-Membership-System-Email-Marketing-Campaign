<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = DB::table('users')->where('email', $this->email)->first();

        if (!$user) {
            $this->addError('email', __('We can\'t find a user with that email address.'));

            return;
        }

        $lastResetRequest = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if ($lastResetRequest) {
            $lastRequestTime = Carbon::parse($lastResetRequest->created_at);
            $nextAllowedRequestTime = $lastRequestTime->addMinutes(5);

            if ($nextAllowedRequestTime->isFuture()) {
                $remainingTime = $nextAllowedRequestTime->diffForHumans([
                    'parts' => 2,
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                ]);

                $this->addError('email', "You must wait {$remainingTime} before requesting another password reset.");

                return;
            }
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div
    class="grid relative flex-col justify-center items-center px-8 h-screen sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
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
            <a href="https://gemailapp.com/" class="flex z-20 flex-col gap-2 items-center font-medium lg:hidden"
                wire:navigate>
                <span class="flex justify-center items-center w-9 h-9 rounded-md">
                    <x-application-logo class="text-black fill-current size-9 dark:text-white" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <!-- Logo/Brand -->
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-neutral-900 dark:text-white">Reset Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter your email to receive a reset link
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="space-y-6">
                <div class="space-y-4 rounded-md shadow-sm">
                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-sm font-medium" />
                        <x-text-input wire:model="email" id="email" type="email" name="email" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <button type="submit"
                            class="flex justify-center px-4 py-2 w-full text-sm font-medium text-white bg-blue-600 rounded-lg border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Send Reset Link') }}
                        </button>
                    </div>

                    <div class="text-sm text-center">
                        <span class="text-neutral-600 dark:text-neutral-400">Remember your password? </span>
                        <a class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                            href="{{ route('login') }}" wire:navigate>
                            Sign in here
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>