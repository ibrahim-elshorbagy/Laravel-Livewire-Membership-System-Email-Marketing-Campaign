<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendResetLinkMail;
use Jantinnerezo\LivewireAlert\LivewireAlert;

new #[Layout('layouts.app',['title' => 'Fogot Password | Bulk Email Marketing App'])] class extends Component
{
    public string $email = '';
    use LivewireAlert;
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


        Mail::to($user->email)->queue(new SendResetLinkMail($user));

        $this->reset('email');

        $this->alert('success', 'We have emailed your password reset link!', ['position' => 'bottom-end']);
    }
}; ?>

<div class="grid relative flex-col px-4 md:px-8 min-h-[85vh] sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="hidden relative flex-col p-6 h-full text-white bg-muted lg:flex dark:border-r dark:border-neutral-800">
        <div class="absolute inset-0 bg-[#f7f7f7]"></div>
        <div class="absolute inset-0 bg-center bg-no-repeat bg-contain"
            style="background-image: url('{{ App\Models\Admin\Site\SiteSetting::getAuthImage() }}');">
        </div>


    </div>
    <div class="flex justify-center items-center w-full h-full">
        <div class="mx-auto flex w-full flex-col justify-evenly space-y-6 sm:w-[350px] h-full">
            <a href="{{ route('main-site') }}" class="flex flex-col gap-2 items-center font-medium z-5 lg:hidden"
                wire:navigate>
                <span class="flex justify-center items-center w-[200px]   rounded-md">
                    <x-application-logo class="text-black fill-current size-9 dark:text-white" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <!-- Logo/Brand -->
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-neutral-900 dark:text-white">Reset Password</h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Enter your email to receive a reset link
                </p>
            </div>


            <form wire:submit="sendPasswordResetLink" class="space-y-6">
                <div class="space-y-4">
                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-sm font-medium" />
                        <x-text-input wire:model="email" id="email" type="email" name="email" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <button type="submit"
                            class="flex justify-center px-4 py-2 w-full text-sm font-medium text-white bg-blue-600 rounded-lg border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Send Reset Link
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
