<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use LucasDotVin\Soulbscription\Models\Plan;

new #[Layout('layouts.app')] class extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $validated['image_url'] = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

            $user = User::create($validated);
            $user->assignRole('user');
            $trialPlan = Plan::find(1);
                if ($trialPlan) {
                $user->subscribeTo($trialPlan);
            }
            Auth::login($user);

            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
}; ?>

<div class="flex justify-center items-center px-4 py-12 min-h-screen bg-gradient-to-br sm:px-6 lg:px-8">
    <div
        class="p-4 space-y-8 w-full max-w-md bg-white rounded-2xl shadow-xl dark:shadow dark:shadow-gray-100 dark:bg-neutral-800">
        <!-- Logo/Brand -->
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-neutral-900 dark:text-white">Create an account</h2>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Start your journey with us</p>
        </div>
        <form wire:submit="register" class="mt-8 space-y-6">
            <div class="space-y-4 rounded-md shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div>
                        <x-input-label for="first_name" :value="__('First Name')" class="text-sm font-medium" />
                        <x-text-input wire:model="first_name" id="first_name" class="block w-full" type="text"
                            name="first_name" required autofocus autocomplete="given-name" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>

                    <!-- Last Name -->
                    <div>
                        <x-input-label for="last_name" :value="__('Last Name')" class="text-sm font-medium" />
                        <x-text-input wire:model="last_name" id="last_name" class="block w-full" type="text"
                            name="last_name" required autocomplete="family-name" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <x-input-label for="username" :value="__('Username')" class="text-sm font-medium" />
                    <x-text-input wire:model="username" id="username" class="block w-full" type="text" name="username"
                        required autocomplete="username" />
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-sm font-medium" />
                    <x-text-input wire:model="email" id="email" class="block w-full" type="email" name="email" required
                        autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-sm font-medium" />
                    <x-text-input wire:model="password" id="password" class="block w-full" type="password"
                        name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')"
                        class="text-sm font-medium" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full"
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div>
                    <button type="submit"
                        class="flex justify-center px-4 py-2 w-full text-sm font-medium text-white bg-blue-600 rounded-lg border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>

                <div class="text-sm text-center">
                    <span class="text-neutral-600 dark:text-neutral-400">Already have an account? </span>
                    <a class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                        href="{{ route('login') }}" wire:navigate>
                        Sign in here
                    </a>
                </div>
        </form>
    </div>
</div>