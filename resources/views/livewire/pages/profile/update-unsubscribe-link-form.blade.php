<?php
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $unsubscribe_email = '';
    public string $unsubscribe_link = '';
    public bool $unsubscribe_status = false;

    /**
    * Mount the component.
    */
    public function mount(): void
    {
        $user = Auth::user();
        $userInfo = $user->userInfo ?? new UserInfo();
        $this->unsubscribe_email = $userInfo->unsubscribe_email ?? '';
        $this->unsubscribe_link = $userInfo->unsubscribe_link ?? '';
        $this->unsubscribe_status = $userInfo->unsubscribe_status ?? false;
    }

    /**
    * Update the unsubscribe information for the currently authenticated user.
    */
    public function updateUnsubscribeInfo(): void
    {
        $user = Auth::user();

        $rules = [
            'unsubscribe_status' => ['boolean'],
        ];

        if ($this->unsubscribe_status) {
            $rules['unsubscribe_email'] = ['required', 'email', 'max:255'];
            $rules['unsubscribe_link'] = ['required', 'url', 'max:255'];
        } else {
            $rules['unsubscribe_email'] = ['nullable', 'email', 'max:255'];
            $rules['unsubscribe_link'] = ['nullable', 'url', 'max:255'];
        }

        $validated = $this->validate($rules);
        $userInfo = $user->userInfo ?? new UserInfo();
        $userInfo->fill($validated);

        if (!$userInfo->exists) {
            $userInfo->user()->associate($user);
        }

        $userInfo->save();

        $this->dispatch('unsubscribe-info-updated');
    }
}; ?>


<section>
    <form wire:submit="updateUnsubscribeInfo" class="">
        <header class="flex justify-between items-center">
            <div class="flex gap-5 items-center mb-6">
                <i class="fa-solid fa-envelope-open-text fa-2xl"></i>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Unsubscribe Settings
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Include unsubscribe text & email to your campaigns
                    </p>
                </div>
            </div>

            <label for="unsubscribe_status" class="inline-flex gap-3 items-center">
                <input wire:model="unsubscribe_status" id="unsubscribe_status" type="checkbox" class="sr-only peer" role="switch" checked  />
                <span class="text-sm font-medium trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Enable Unsubscribe Header</span>
                <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full   bg-neutral-200 after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-600 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-800 dark:after:bg-neutral-400 dark:peer-checked:bg-green-600 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-green-600" aria-hidden="true"></div>
            </label>

        </header>


        <div class="grid grid-cols-1 gap-6 max-w-xl">
            <!-- Unsubscribe Email -->
            <div>
                <x-input-label for="unsubscribe_email" :value="__('Unsubscribe Email Address')" />
                <x-text-input wire:model="unsubscribe_email" id="unsubscribe_email" type="email"
                    class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    When the user clicks on the "Unsubscribe" link, it will open their default email client and populate the "To" field with
                    the unsubscribe email address (e.g., unsubscribe@example.com).
                </p>
                <x-input-error :messages="$errors->get('unsubscribe_email')" class="mt-2" />
            </div>

            <!-- Unsubscribe Link -->
            <div>
                <x-input-label for="unsubscribe_link" :value="__('Unsubscribe Link')" />
                <x-text-input wire:model="unsubscribe_link" id="unsubscribe_link" type="url"
                    class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    The unsubscribe URL should point to a page or service that processes unsubscribe requests
                </p>
                <x-input-error :messages="$errors->get('unsubscribe_link')" class="mt-2" />
            </div>


        </div>

        <!-- Information Section -->
        <div class="mt-5 max-w-2xl">
            <div class="items-start mb-4">
                <i class="my-2 mr-3 text-yellow-400 fas fa-lightbulb"></i>

                <div class="text-sm text-gray-600 dark:text-gray-400">

                    <p class="mb-2">
                        • This is a great way to comply with email best practices, improve deliverability, and ensure you're respecting your
                        subscribers' preferences.
                    </p>

                    <p class="mb-2">
                        • This method won't display the unsubscribe link in the email body itself, but email clients that support this header will
                        show an unsubscribe button/link directly to the user.
                    </p>

                    <p class="mb-2">
                        • If you want to include the unsubscribe link directly in the body, just add it as plain text or a clickable link in
                        your message design.
                    </p>

                    <p class="mb-2">
                        • Gmail and other email clients may show an "Unsubscribe" button or link directly in the email interface if they detect
                        the unsubscribe details in header, which helps prevent your emails from being marked as spam.
                    </p>

                </div>
            </div>
        </div>

        <div class="flex gap-4 items-center">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="unsubscribe-info-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
