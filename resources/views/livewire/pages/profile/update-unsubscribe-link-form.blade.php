<?php
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;

new class extends Component
{
    public string $unsubscribe_pre_text = '';
    public string $unsubscribe_text = '';
    public string $unsubscribe_link = '';
    public bool $unsubscribe_status = false;

    public function mount(): void
    {
        $user = Auth::user();
        $userInfo = $user->userInfo ?? new UserInfo();

        // Set default values if empty
        $this->unsubscribe_pre_text = $userInfo->unsubscribe_pre_text ?? 'If you no longer wish to receive emails from us, please';
        $this->unsubscribe_text = $userInfo->unsubscribe_text ?? 'unsubscribe here';
        $this->unsubscribe_link = $userInfo->unsubscribe_link ?? '';
        $this->unsubscribe_status = $userInfo->unsubscribe_status ?? false;
    }

    public function resetDefaults(): void
    {
        $this->unsubscribe_pre_text = 'If you no longer wish to receive emails from us, please';
        $this->unsubscribe_text = 'unsubscribe here';
        $this->unsubscribe_link = '';
        $this->unsubscribe_status = false;
    }

    /**
    * Update the unsubscribe information for the currently authenticated user.
    */
    public function updateUnsubscribeInfo(): void
    {
        $user = Auth::user();

        $rules = [
            'unsubscribe_status'    => ['boolean'],
            'unsubscribe_pre_text'  => ['required_if:unsubscribe_status,true', 'string'],
            'unsubscribe_text'      => ['required_if:unsubscribe_status,true', 'string'],
            'unsubscribe_link' => [
                'required_if:unsubscribe_status,true',
                'string',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The Unsubscribe Method must be a valid email or URL.');
                    }
                }
            ],
        ];


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


<section x-data="{disableUnsubscribe:!$wire.unsubscribe_status}">
    <form wire:submit="updateUnsubscribeInfo">
        <header class="flex flex-col justify-between items-center mb-3 md:flex-row">
            <div class="flex gap-5 items-center mb-6">
                <i class="fa-solid fa-envelope-open-text fa-2xl"></i>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Unsubscribe Settings
                    </h2>

                    <p class="mt-1 text-xs text-gray-600 md:text-sm dark:text-gray-400">
                        Include unsubscribe text & email to your campaigns
                    </p>
                </div>
            </div>

            <label for="unsubscribe_status" class="inline-flex gap-3 items-center">
                <input wire:model="unsubscribe_status" x-bind:click="disableUnsubscribe = !$wire.unsubscribe_status"
                    id="unsubscribe_status" type="checkbox" class="sr-only peer" role="switch" checked />
                <span
                    class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Enable
                    Unsubscribe Header</span>
                <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full   bg-neutral-200 after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-600 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-800 dark:after:bg-neutral-400 dark:peer-checked:bg-green-600 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-green-600"
                    aria-hidden="true"></div>
            </label>

        </header>


        <div class="grid grid-cols-1 gap-4 max-w-xl">
            <!-- Unsubscribe Pre Text -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="unsubscribe_pre_text" :value="__('Unsubscribe Pre Text')" /><span
                        class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="unsubscribe_pre_text" id="unsubscribe_pre_text"
                    x-bind:disabled="disableUnsubscribe" class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This Will Be the Text before the Unsubscribe Link Button.
                </p>
                <x-input-error :messages="$errors->get('unsubscribe_pre_text')" class="mt-2" />
            </div>
            <!-- Unsubscribe Email -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="unsubscribe_text" :value="__('Unsubscribe Button Text')" /><span
                        class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="unsubscribe_text" id="unsubscribe_text"
                    x-bind:disabled="disableUnsubscribe" class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This Will Be the Text Of Unsubscribe Link Button.
                </p>
                <x-input-error :messages="$errors->get('unsubscribe_text')" class="mt-2" />
            </div>
            <!-- Unsubscribe Link -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="unsubscribe_link" :value="__('Unsubscribe Link Or Email')" /><span
                        class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="unsubscribe_link" id="unsubscribe_link"
                    x-bind:disabled="disableUnsubscribe" class="block mt-1 w-full"
                    placeholder="Example: https://xxx.com/unsub.html OR unsub@xxx.com" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    Please enter the URL for unsubscribing from or provide the email address you wish to use for the
                    unsubscription request.
                </p>
                <x-input-error :messages="$errors->get('unsubscribe_link')" class="mt-2" />
            </div>

            <!-- Preview -->
            <div class="mt-4">
                <h4 class="mb-2 text-xs font-medium text-gray-900 md:text-sm dark:text-gray-100">Preview:</h4>
                <div
                    class="p-4 bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="unsubscribe-container">
                        <p class="unsubscribe-text">
                            {{ $unsubscribe_pre_text }}
                            @if(filter_var($unsubscribe_link, FILTER_VALIDATE_EMAIL))
                            <a href="mailto:{{ $unsubscribe_link }}">
                                {{ $unsubscribe_text }}
                            </a>.
                            @elseif(filter_var($unsubscribe_link, FILTER_VALIDATE_URL))
                            <a href="{{ $unsubscribe_link }}">
                                {{ $unsubscribe_text }}
                            </a>.
                            @else
                            <a href="#">
                                {{ $unsubscribe_text }}
                            </a>.
                            @endif
                        </p>
                    </div>
                </div>
            </div>


        </div>





        <!-- Information Section -->
        <div class="p-4 my-6 bg-gray-50 rounded-lg border border-gray-200 dark:bg-neutral-900 dark:border-neutral-700">
            <div class="items-start mb-4 ml-2">
                <div class="flex gap-2 items-center mb-4">
                    <i class="my-2 text-yellow-400 fas fa-lightbulb"></i>
                    <h3 class="font-medium text-gray-900 text-md dark:text-gray-100">
                        Notes
                    </h3>
                </div>

                <div class="text-xs text-gray-600 md:text-sm dark:text-gray-400">

                    <p class="mb-2">
                        • GeMailApp will automatically generate a clickable unsubscribe link and add to it to all your
                        email designs.
                    </p>

                    <p class="mb-2">
                        • This is a great way to comply with email best practices, improve deliverability, and ensure
                        you're respecting your subscribers' preferences.
                    </p>

                </div>
            </div>



        </div>

        <div class="flex gap-4 items-center">
            <x-primary-button>Save</x-primary-button>
            <x-primary-info-button type="button" wire:click="resetDefaults">
                Reset Defaults
            </x-primary-info-button>

            <x-action-message class="me-3" on="unsubscribe-info-updated">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>
