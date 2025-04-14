<?php
use App\Models\User;
use App\Models\UserBouncesInfo;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;

new class extends Component
{
    public string $bounce_inbox = '';
    public string $bounce_inbox_password = '';
    public string $mail_server = '';
    public string $imap_port = '';
    public bool $bounce_status = false;

    public function mount(): void
    {
        $user = Auth::user();
        $userBounces = $user->userBouncesInfo ?? new UserBouncesInfo();

        $this->bounce_inbox = $userBounces->bounce_inbox ?? '';
        $this->bounce_inbox_password = $userBounces->bounce_inbox_password ?? '';
        $this->mail_server = $userBounces->mail_server ?? '';
        $this->imap_port = $userBounces->imap_port ?? '993';
        $this->bounce_status = $userBounces->bounce_status ?? false;
    }

    public function resetDefaults(): void
    {
        $this->bounce_inbox = '';
        $this->bounce_inbox_password = '';
        $this->mail_server = '';
        $this->imap_port = '993';
        $this->bounce_status = false;
    }

    public function updateBounceInfo(): void
    {
        $user = Auth::user();

        $rules = [
            'bounce_status' => ['boolean'],
            'bounce_inbox' => ['required_if:bounce_status,true', 'email'],
            'bounce_inbox_password' => ['required_if:bounce_status,true', 'string'],
            'mail_server' => ['required_if:bounce_status,true', 'string'],
            'imap_port' => ['required_if:bounce_status,true', 'string'],
        ];

        $validated = $this->validate($rules);

        UserBouncesInfo::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $this->dispatch('bounce-info-updated');
    }
}; ?>

<section x-data="{disableBounce:!$wire.bounce_status}">
    <form wire:submit="updateBounceInfo">
        <header class="flex flex-col justify-between items-center mb-3 md:flex-row">
            <div class="flex gap-5 items-center mb-6">
                <i class="fa-solid fa-envelope-circle-check fa-2xl"></i>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Email Bounce Settings
                    </h2>

                    <p class="mt-1 text-xs text-gray-600 md:text-sm dark:text-gray-400">
                        Configure bounce email settings to handle undelivered emails
                    </p>
                </div>
            </div>

            <label for="bounce_status" class="inline-flex gap-3 items-center">
                <input wire:model="bounce_status" x-bind:click="disableBounce = !$wire.bounce_status" id="bounce_status"
                    type="checkbox" class="sr-only peer" role="switch" checked />
                <span
                    class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Enable
                    Bounce Handling</span>
                <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full   bg-neutral-200 after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-600 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-800 dark:after:bg-neutral-400 dark:peer-checked:bg-green-600 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-green-600"
                    aria-hidden="true"></div>
            </label>
        </header>

        <div class="grid grid-cols-1 gap-4 mb-6 max-w-xl">
            <!-- Bounce Inbox -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="bounce_inbox" :value="__('Bounce Inbox')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="bounce_inbox" id="bounce_inbox" x-bind:disabled="disableBounce"
                    class="block mt-1 w-full" placeholder="bounce@localhost" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This is the inbox which emails will be sent to.
                </p>
                <x-input-error :messages="$errors->get('bounce_inbox')" class="mt-2" />
            </div>

            <!-- Bounce Inbox Password -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="bounce_inbox_password" :value="__('Bounce Inbox Password')" /><span
                        class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="bounce_inbox_password" id="bounce_inbox_password" type="password"
                    x-bind:disabled="disableBounce" class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This password to access the inbox.
                </p>
                <x-input-error :messages="$errors->get('bounce_inbox_password')" class="mt-2" />
            </div>

            <!-- Mail Server -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="mail_server" :value="__('Mail Server')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="mail_server" id="mail_server" x-bind:disabled="disableBounce"
                    class="block mt-1 w-full" placeholder="mail.localhost" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This is the domain your email inbox is hosted. Most likely mail.yourdomain.com
                </p>
                <x-input-error :messages="$errors->get('mail_server')" class="mt-2" />
            </div>

            <!-- IMAP Port -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="imap_port" :value="__('IMAP Port')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="imap_port" id="imap_port" x-bind:disabled="disableBounce"
                    class="block mt-1 w-full" placeholder="993" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    The bounce checker requires an IMAP connection. Most IMAP ports are 993.
                </p>
                <x-input-error :messages="$errors->get('imap_port')" class="mt-2" />
            </div>
        </div>

        <!-- Information Section -->
        {{-- <div
            class="p-4 my-6 bg-gray-50 rounded-lg border border-gray-200 dark:bg-neutral-900 dark:border-neutral-700">
            <div class="items-start mb-4 ml-2">
                <div class="flex gap-2 items-center mb-4">
                    <i class="my-2 text-yellow-400 fas fa-lightbulb"></i>
                    <h3 class="font-medium text-gray-900 text-md dark:text-gray-100">
                        Notes
                    </h3>
                </div>

                <div class="text-xs text-gray-600 md:text-sm dark:text-gray-400">
                    <p class="mb-2">
                        • GeMailApp will automatically check for bounced emails and update your email lists accordingly.
                    </p>

                    <p class="mb-2">
                        • This helps maintain clean email lists and improves your email deliverability rates.
                    </p>
                </div>
            </div>
        </div> --}}

        <div class="flex gap-4 items-center">
            <x-primary-button>Save</x-primary-button>
            <x-primary-info-button type="button" wire:click="resetDefaults">
                Reset Defaults
            </x-primary-info-button>

            <x-action-message class="me-3" on="bounce-info-updated">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>