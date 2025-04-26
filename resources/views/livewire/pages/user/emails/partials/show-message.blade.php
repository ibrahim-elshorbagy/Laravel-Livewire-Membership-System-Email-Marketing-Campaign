<div class="p-2 w-full min-h-screen md:p-4 lg:p-6">
    <div class="mx-auto space-y-4 max-w-7xl md:space-y-6">
        <!-- Preview Section -->
        <div
            class="overflow-hidden w-full bg-white rounded-lg border border-gray-200 shadow-sm dark:bg-neutral-800 md:rounded-xl dark:border-neutral-700">
            <!-- Preview Header -->
            <div
                class="flex flex-col gap-2 justify-between items-start p-4 border-b border-gray-200 md:flex-row md:items-center dark:border-neutral-700">
                <h3
                    class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-gray-900 to-gray-600 md:text-2xl dark:from-gray-100 dark:to-gray-300">
                    Preview: {{ $message->email_subject }}
                </h3>
                <div class="flex gap-5 items-center">
                    <x-primary-info-link href="{{ route('user.email-messages') }}" wire:navigate>
                        Back To Messages
                    </x-primary-info-link>

                    <a href="{{ route('user.emails.message.form', $message->id) }}"
                        class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                        Edit
                    </a>

                </div>
            </div>

            <!-- Preview Content -->
            <div class="w-full h-[1000px]">
                <iframe id="preview-frame" class="w-full h-full"
                    sandbox="allow-same-origin allow-scripts allow-popups allow-forms" srcdoc="{{ $message->message_html }}">
                </iframe>
            </div>
        </div>
        <!-- Header Section -->
        <div class="w-full bg-white rounded-lg border border-gray-200 shadow-sm dark:bg-neutral-800 md:rounded-xl dark:border-neutral-700">
            <div class="p-3 md:p-4 lg:p-6">
                <!-- Header with Title and Back Button -->
                <div class="flex flex-col gap-4 justify-between mb-6 md:flex-row md:items-center">
                    <h2
                        class="">

                    </h2>

                    <div>

                    </div>
                </div>

                <!-- Message Details Grid -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

                    <!-- Plain Text Version Card -->
                    <div
                        class="col-span-1 p-4 w-full bg-gray-50 rounded-lg transition-all md:col-span-2 lg:col-span-3 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Plain Text</h3>
                        <div
                            class="overflow-auto p-3 w-full bg-white rounded-lg dark:bg-neutral-800 md:p-4">
                            <pre
                                class="font-mono text-xs text-gray-700 whitespace-pre-wrap md:text-sm dark:text-gray-300">
                                {{ $message->message_plain_text ?: 'No plain text content' }}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
