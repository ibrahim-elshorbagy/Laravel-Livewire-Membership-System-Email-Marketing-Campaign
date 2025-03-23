<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate"> {{
            $email_id ? 'Edit System Email' : 'New System Email' }} </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('admin.site-system-emails') }}" wire:navigate> Back To System Emails
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="saveEmail" class="space-y-4">
        <div class="grid grid-cols-1 gap-6 h-full lg:grid-cols-2">
            <!-- Form Section -->
            <div class="overflow-y-auto space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="name">Name</x-input-label>
                        <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="slug">Slug</x-input-label>
                        <x-text-input wire:model="slug" id="slug" type="text" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>
                </div>
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
                    <x-input-label for="message_html">HTML Template</x-input-label>
                    <div class="mt-1 space-y-2">
                        <!-- Make container resizable -->
                        <div id="editor-container" class="overflow-hidden rounded-md border dark:border-neutral-700"
                            style="height: 400px; min-height: 200px; max-height: 800px; resize: vertical;"> </div>
                        <!-- Hidden textarea bound to Livewire --> <textarea id="editor" wire:model.live="message_html"
                            class="hidden"></textarea>
                    </div>
                    <x-input-error :messages="$errors->get('message_html')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="message_plain_text">Message Plain Text</x-input-label>
                    <x-primary-textarea wire:model="message_plain_text" id="message_plain_text" rows="16"
                        class="block mt-1 w-full"> </x-primary-textarea>
                    <x-input-error :messages="$errors->get('message_plain_text')" class="mt-2" />
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
                                    You can personalize system emails using template variables that will be replaced
                                    with actual values when the email is sent.
                                    Include these variables in your email template to make it dynamic and personalized.
                                </p>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center">
                                        <code
                                            class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%FullName%</code>
                                        <span class="ml-2">Replaced with the recipient's full name</span>
                                    </div>
                                    <div class="flex items-center">
                                        <code
                                            class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%Email%</code>
                                        <span class="ml-2">Replaced with the recipient's email address</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex sticky bottom-0 justify-end py-4 space-x-3 bg-neutral-50 dark:bg-neutral-900">
                    <x-secondary-button type="button" wire:navigate href="{{ route('admin.site-system-emails') }}">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit"> {{ $email_id ? 'Update Email' : 'Create Email' }}
                    </x-primary-create-button>
                </div>
            </div>
            <!-- Preview Section -->
            <div class="rounded-lg border dark:border-neutral-700">
                <div class="flex justify-between items-center p-4 border-b dark:border-neutral-700">
                    <h3 class="text-lg font-semibold">Preview</h3>
                    <button type="button" x-data @click="updatePreview($wire.message_html)"
                        class="px-3 py-1 text-sm text-blue-600 rounded-md border border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900">
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
@vite('resources/js/codeEditor.js')
@endpush