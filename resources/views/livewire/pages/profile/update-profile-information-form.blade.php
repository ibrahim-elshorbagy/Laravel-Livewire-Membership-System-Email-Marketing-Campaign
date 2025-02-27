<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $username = '';
    public string $company = '';
    public string $country = '';
    public string $whatsapp = '';
    public string $timezone = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->email = $user->email ?? '';
        $this->username = $user->username ?? '';
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->company = $user->company ?? '';
        $this->country = $user->country ?? '';
        $this->whatsapp = $user->whatsapp ?? '';
        $this->timezone = $user->timezone ?? config('app.timezone');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['string', 'regex:/^\+?\d{10,13}$/'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->first_name);
        $this->dispatch('refresh-user-profile-display', [
        'image_url' => $user->image_url,
        'first_name' => $user->first_name,
        ]);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header class="flex gap-5 items-center">
        <i class="fa-solid fa-info fa-2xl"></i>
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Profile Information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Update your account's profile information") }}
            </p>
        </div>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            <div >
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input wire:model="first_name" id="first_name" name="first_name" type="text" class="block mt-1 w-full" required autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div >
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input wire:model="last_name" id="last_name" name="last_name" type="text" class="block mt-1 w-full" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div >
                <x-input-label for="company" :value="__('Company')" />
                <x-text-input wire:model="company" id="company" name="company" type="text" class="block mt-1 w-full"  autocomplete="organization" />
                <x-input-error class="mt-2" :messages="$errors->get('company')" />
            </div>

            <div >
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input wire:model="country" id="country" name="country" type="text" class="block mt-1 w-full"  autocomplete="country" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>

            <div >
                <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                <x-text-input placeholder="+01096325697" wire:model="whatsapp" id="whatsapp" name="whatsapp" type="text" class="block mt-1 w-full"  autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
            </div>

            @role('user')
            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                <x-primary-select-input wire:model="timezone" id="timezone" name="timezone"
                    class="block mt-1 w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-900/50 dark:text-white focus:border-sky-500 focus:ring-sky-500">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </x-primary-select-input>
                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
            </div>
            @endrole

        </div>

        <div>
            <div class="flex flex-col gap-1 mb-2">
                <x-input-label for="email" :value="__('Email')" />
                <div class="flex items-center mt-1 text-gray-900 dark:text-gray-100">
                    <i class="fas fa-envelope"></i>
                    <p class="ms-2">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <x-input-label for="username" :value="__('Username')" />
                <div class="flex items-center mt-1 text-gray-900 dark:text-gray-100">
                    <i class="fas fa-user"></i>
                    <p class="ms-2">{{ auth()->user()->username }}</p>
                </div>
            </div>
            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="text-sm text-gray-600 underline rounded-md dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex gap-4 items-center">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
