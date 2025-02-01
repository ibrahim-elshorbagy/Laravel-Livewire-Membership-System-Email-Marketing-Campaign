<div class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            Campaign
        </h2>

    </header>
    <form wire:submit.prevent="saveCampaign" class="space-y-4">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <x-input-label for="campaign_title">Campaign Title</x-input-label>
                <x-text-input wire:model="campaign_title" id="campaign_title" type="text" class="block w-full mt-1" />
                <x-input-error :messages="$errors->get('campaign_title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email_subject">Email Subject</x-input-label>
                <x-text-input wire:model="email_subject" id="email_subject" type="text" class="block w-full mt-1" />
                <x-input-error :messages="$errors->get('email_subject')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="message_html">Message HTML</x-input-label>
                <x-primary-textarea wire:model="message_html" id="message_html" rows="8"
                    class="block w-full mt-1 rounded-md"></x-primary-textarea>
                <x-input-error :messages="$errors->get('message_html')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="message_plain_text">Message Plain Text</x-input-label>
                <x-primary-textarea wire:model="message_plain_text" id="message_plain_text" rows="8"
                    class="block w-full mt-1 rounded-md"></x-primary-textarea>
                <x-input-error :messages="$errors->get('message_plain_text')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sender_name">Sender Name</x-input-label>
                <x-text-input wire:model="sender_name" id="sender_name" type="text" class="block w-full mt-1" />
            </div>

            <div>
                <x-input-label for="reply_to_email">Reply To Email</x-input-label>
                <x-text-input wire:model="reply_to_email" id="reply_to_email" type="email" class="block w-full mt-1" />
            </div>

            <div>
                <x-input-label for="sending_status">Sending Status</x-input-label>
                <x-primary-select-input wire:model="sending_status" id="sending_status" class="block w-full mt-1 rounded-md">
                    <option value="PAUSE">Pause</option>
                    <option value="RUN">Run</option>
                </x-primary-select-input>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-create-button type="submit">
                Save Campaign
            </x-primary-create-button>
        </div>
    </form>
</div>

