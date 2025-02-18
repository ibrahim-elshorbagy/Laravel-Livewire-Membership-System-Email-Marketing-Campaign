<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $message_id ? 'Edit Message' : 'New Message' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('user.email-messages') }}" wire:navigate
                class="inline-flex flex-col items-center px-2 py-1 text-xs font-semibold tracking-widest text-white transition duration-150 ease-in-out border rounded-md cursor-pointer md:px-4 md:py-2 text-nowrap bg-sky-600 dark:bg-sky-900 group border-sky-300 dark:border-sky-700 dark:text-sky-300 hover:bg-sky-700 dark:hover:bg-sky-100 focus:bg-sky-700 dark:focus:bg-sky-100 active:bg-sky-900 dark:active:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800">
                Back To Messages
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="saveMessage" class="space-y-4">
        <div class="grid h-full grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Form Section -->
            <div class="space-y-4 overflow-y-auto ">
                <div>
                    <x-input-label for="message_title">Message Title</x-input-label>
                    <x-text-input wire:model="message_title" id="message_title" type="text"
                        class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('message_title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email_subject">Email Subject</x-input-label>
                    <x-text-input wire:model="email_subject" id="email_subject" type="text" class="block w-full mt-1" />
                    <x-input-error :messages="$errors->get('email_subject')" class="mt-2" />
                </div>

                <div wire:ignore>
                    <x-input-label for="message_html">HTML Template</x-input-label>
                    <div class="mt-1 space-y-2">
                        <!-- Make container resizable -->
                        <div id="editor-container" class="overflow-hidden border rounded-md dark:border-neutral-700"
                            style="height: 400px; min-height: 200px; max-height: 800px; resize: vertical;">
                        </div>
                        <!-- Hidden textarea bound to Livewire -->
                        <textarea id="editor" wire:model.live="message_html" class="hidden"></textarea>
                    </div>
                    <x-input-error :messages="$errors->get('message_html')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="message_plain_text">Message Plain Text</x-input-label>
                    <x-primary-textarea wire:model="message_plain_text" id="message_plain_text" rows="4"
                        class="block w-full mt-1">
                    </x-primary-textarea>
                    <x-input-error :messages="$errors->get('message_plain_text')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="sender_name">Sender Name</x-input-label>
                        <x-text-input wire:model="sender_name" id="sender_name" type="text" class="block w-full mt-1" />
                        <x-input-error :messages="$errors->get('sender_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reply_to_email">Reply To Email</x-input-label>
                        <x-text-input wire:model="reply_to_email" id="reply_to_email" type="email"
                            class="block w-full mt-1" />
                        <x-input-error :messages="$errors->get('reply_to_email')" class="mt-2" />
                    </div>
                </div>

                {{-- <div>
                    <x-input-label for="sending_status">Sending Status</x-input-label>
                    <x-primary-select-input wire:model="sending_status" id="sending_status" class="block w-full mt-1">
                        <option value="PAUSE">Pause</option>
                        <option value="RUN">Run</option>
                    </x-primary-select-input>
                </div> --}}

                <div class="sticky bottom-0 flex justify-end py-4 space-x-3 bg-neutral-50 dark:bg-neutral-900">
                    <x-secondary-button type="button" wire:navigate href="{{ route('user.email-messages') }}">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit">
                        {{ $message_id ? 'Update  Message' : 'Create Message' }}
                    </x-primary-create-button>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="border rounded-lg dark:border-neutral-700">
                <div class="flex items-center justify-between p-4 border-b dark:border-neutral-700">
                    <h3 class="text-lg font-semibold">Preview</h3>
                    <button type="button" x-data @click="updatePreview($wire.message_html)"
                        class="px-3 py-1 text-sm text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900">
                        Refresh Preview
                    </button>
                </div>
                <div class="h-[calc(100%-60px)] overflow-hidden">
                    <iframe id="preview-frame" class="w-full h-full bg-white border-0 dark:bg-neutral-800"
                        sandbox="allow-same-origin allow-scripts allow-popups allow-forms allow-presentation"
                        referrerpolicy="no-referrer">
                    </iframe>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
@vite('resources/js/codeEditor.js')
@endpush
