<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate"> {{
            $message_id ? 'Edit Message' : 'New Message' }} </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('user.email-messages') }}" wire:navigate> Back To Messages
            </x-primary-info-button>
        </div>
    </header>



    <form wire:submit.prevent="saveMessage" class="space-y-4" id="messageForm"
        x-data="{ localMessageHtml: @js($message_html) }">
        <div class="grid grid-cols-1 gap-6 h-full lg:grid-cols-2">
            <!-- Form Section -->
            <div class="overflow-y-auto space-y-4">
                <div>
                    <x-input-label for="message_title">Message Title</x-input-label>
                    <x-text-input wire:model="message_title" id="message_title" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('message_title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email_subject">Email Subject</x-input-label>
                    <x-text-input wire:model="email_subject" id="email_subject" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('email_subject')" class="mt-2" />
                </div>
                <div wire:ignore>
                    <x-input-label for="editor">HTML Template</x-input-label>
                    <div class="mt-1 space-y-2">
                        <div id="editor" class="min-h-[350px]"></div>
                    </div>
                </div>

                <div class="no-tailwindcss-support-display">
                    <x-input-label for="message_plain_text">Message Plain Text</x-input-label>
                    <x-primary-textarea wire:model="message_plain_text" id="message_plain_text" rows="16"
                        class="block mt-1 w-full"> </x-primary-textarea>
                    {{--
                    <x-input-error :messages="$errors->get('message_plain_text')" class="mt-2" /> --}}
                </div>

                <div class="p-4 mb-4 bg-white rounded-lg border dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="w-5 h-5 text-blue-500 fas fa-info-circle"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Message Personalization
                            </h3>
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                <p class="mb-3">
                                    A powerful feature of GeMailApp is the ability to personalize outgoing messages with
                                    the name and/or email address of
                                    the recipient.
                                    To take advantage of this feature you need only specify the personalization fields
                                    in your
                                    text of your message. As the message is sent the fields are replaced with the
                                    appropriate value for each
                                    message recipient.
                                </p>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center">
                                        <code
                                            class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%FullName%</code>
                                        <span class="ml-2">Replaced with the Full Name of the member</span>
                                    </div>

                                    <p class="mb-3">
                                        Full Name field is filled automatically when retrieving e-mail addresses if the
                                        information is available.
                                    </p>

                                    <div class="flex items-center">
                                        <code
                                            class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%Email%</code>
                                        <span class="ml-2">Replaced with the e-mail address of the user</span>
                                    </div>
                                </div>




                            </div>
                        </div>
                    </div>
                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="sender_name">Sender Name</x-input-label>
                        <x-text-input wire:model="sender_name" id="sender_name" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('sender_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="reply_to_email">Reply To Email</x-input-label>
                        <x-text-input wire:model="reply_to_email" id="reply_to_email" type="email"
                            class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('reply_to_email')" class="mt-2" />
                    </div>
                </div>
                <div class="flex sticky bottom-0 justify-end py-4 space-x-3 bg-neutral-50 dark:bg-neutral-900">
                    <x-secondary-button type="button" wire:navigate href="{{ route('user.email-messages') }}"> Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit"> {{ $message_id ? 'Update Message' : 'Create Message' }}
                    </x-primary-create-button>
                </div>
            </div>




            <!-- Preview Section -->
            <div class="h-full rounded-lg border dark:border-neutral-700">
                <div class="flex justify-between items-center p-4 border-b dark:border-neutral-700">
                    <h3 class="text-lg font-semibold">Preview</h3>
                </div>
                <div class="h-[calc(100vh-200px)] md:h-[calc(100%-60px)] overflow-hidden">
                    <iframe id="preview-frame" class="w-full h-full bg-white border-0 dark:bg-neutral-800"
                        sandbox="allow-same-origin allow-scripts allow-popups allow-forms" referrerpolicy="no-referrer">
                    </iframe>
                </div>
            </div>

        </div>
    </form>
    @if ($errors->any())
    <div class="p-4 mt-4 bg-red-50 rounded-md border border-red-200 dark:bg-red-900/20 dark:border-red-800">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="w-5 h-5 text-red-400 fas fa-times-circle"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                    There were errors with your submission
                </h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <ul class="pl-5 space-y-1 list-disc">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>





@push('scripts')
@vite('resources/js/AdvanceCodeEditor.js')
<script>
    document.addEventListener('livewire:initialized', function() {
        // Remove the DOMContentLoaded initialization from here
        const form = document.getElementById('messageForm');
        if (form && !window.advanceCodeEditor.initialized) {
            window.advanceCodeEditor.init();

            // Handle form submission
            form.addEventListener('submit', function() {
                @this.set('message_html', window.advanceCodeEditor.getContent());
            });

            // Handle Livewire updates
            Livewire.on('message-updated', () => {
                if (window.advanceCodeEditor) {
                    window.advanceCodeEditor.destroyEditor();
                    setTimeout(() => {
                        window.advanceCodeEditor.init();
                    }, 100);
                }
            });
        }
    });
</script>
@endpush

{{-- @push('scripts')
@vite('resources/js/codeEditor.js')
@endpush --}}
