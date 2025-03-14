<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\User;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $login  = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $field = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        // Find the user first
        $user = User::where($field, $this->login)->withTrashed()->first();

        // Check if user exists
        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'form.login' => trans('auth.failed'),
            ]);
        }

        // Check if user is soft deleted
        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'form.login' => __('This account has been deleted.'),
            ]);
        }

        // Check if user is active
        if (!$user->active) {
            throw ValidationException::withMessages([
                'form.login' => __('This account is not active.'),
            ]);
        }

        if (! Auth::attempt([
            $field => $this->login,
            'password' => $this->password
        ], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }
    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login).'|'.request()->ip());
    }
}
