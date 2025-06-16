<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">Send Email</h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-link href="{{ route('admin.users') }}" wire:navigate> Back To System Users
            </x-primary-info-link>
        </div>
    </header>

    <form wire:submit.prevent="sendEmail" id="messageForm" class="space-y-4">
        <div class="grid grid-cols-1 gap-6 h-full lg:grid-cols-2">
            <!-- Form Section -->
            <div class="overflow-y-auto space-y-4">
                <div>
                    <x-input-label for="email_subject">Email Subject</x-input-label>
                    <x-text-input wire:model="email_subject" id="email_subject" type="text" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('email_subject')" class="mt-2" />
                </div>
                <div class="mb-4" x-data="{ activeEditor: @entangle('activeEditor').live }">
                    <div class="flex justify-between items-center mb-4">
                        <x-input-label>HTML Template</x-input-label>
                        <div class="flex items-center space-x-4">
                            <button type="button"
                                @click="$wire.set('activeEditor', 'advanced'); $dispatch('editor-changed', { editor: 'advanced' })"
                                :class="{'bg-blue-500 text-white': activeEditor === 'advanced', 'bg-gray-200 dark:bg-neutral-700': activeEditor != 'advanced'}"
                                class="px-4 py-2 rounded-md transition-colors">
                                Advanced Editor
                            </button>
                            <button type="button"
                                @click="$wire.set('activeEditor', 'code'); $dispatch('editor-changed', { editor: 'code' })"
                                :class="{'bg-blue-500 text-white': activeEditor === 'code', 'bg-gray-200 dark:bg-neutral-700': activeEditor != 'code'}"
                                class="px-4 py-2 rounded-md transition-colors">
                                Code Editor
                            </button>
                        </div>
                    </div>

                    <div wire:ignore x-show="activeEditor === 'advanced'">
                        <div class="mt-1 space-y-2">
                            <div id="text-editor" class="min-h-[350px]"></div>
                        </div>
                    </div>

                    <div wire:ignore x-show="activeEditor === 'code'">
                        <div class="mt-1 space-y-2">
                            <div id="editor-container" class="overflow-hidden rounded-md border dark:border-neutral-700"
                                style="height: 400px; min-height: 200px; max-height: 800px; resize: vertical;"> </div>
                            <textarea id="editor" wire:model.live="message_html" class="hidden"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex sticky bottom-0 justify-end py-4 space-x-3 bg-neutral-50 dark:bg-neutral-900">
                    <x-secondary-button type="button" wire:navigate href="{{ route('admin.site-system-emails') }}">
                        Cancel
                    </x-secondary-button>
                    <x-primary-create-button type="submit"> Send
                    </x-primary-create-button>
                </div>
            </div>
            <!-- Preview Section -->
            <div class="h-full rounded-lg border dark:border-neutral-700">
                <div class="flex justify-between items-center p-4 border-b dark:border-neutral-700">
                    <h3 class="text-lg font-semibold">Preview</h3>
                    <button type="button" x-data @click="updatePreview($wire.message_html)"
                        class="px-3 py-1 text-sm text-blue-600 rounded-md border border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900">
                        Refresh Preview
                    </button>
                </div>
                <div class="h-[calc(100vh-200px)] md:h-[calc(100%-60px)] overflow-hidden">
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



    @include('livewire.pages.admin.site-settings.system.system-emails.partials.rules')



</div>
@push('scripts')
@vite('resources/js/AdvanceTinyMCE.js')
<script>
    document.addEventListener('livewire:initialized', function() {
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


@push('scripts')
@vite('resources/js/codeEditor.js')
@endpush
