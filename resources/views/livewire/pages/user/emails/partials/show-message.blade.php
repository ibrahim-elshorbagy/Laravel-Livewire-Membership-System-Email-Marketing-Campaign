<div class="w-full min-h-screen p-2 md:p-4 lg:p-6">
    <div class="mx-auto space-y-4 max-w-7xl md:space-y-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-neutral-800 md:rounded-xl dark:border-neutral-700">
            <div class="p-3 md:p-4 lg:p-6">
                <!-- Header with Title and Back Button -->
                <div class="flex flex-col justify-between gap-4 mb-6 md:flex-row md:items-center">
                    <h2
                        class="text-xl font-bold text-transparent md:text-2xl lg:text-3xl bg-gradient-to-r from-gray-900 to-gray-600 dark:from-gray-100 dark:to-gray-300 bg-clip-text">
                        {{ $message->message_title }}
                    </h2>

                    <div>
                    <x-primary-info-button href="{{ route('user.email-messages') }}" wire:navigate>
                        Back To Messages
                    </x-primary-info-button>
                    <x-primary-info-button href="{{ route('user.emails.message.form', $message->id) }}">
                        Edit
                    </x-primary-info-button>
                    </div>
                </div>

                <!-- Message Details Grid -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Message Title Card -->
                    <div class="w-full p-4 transition-all rounded-lg bg-gray-50 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Message Title</h3>
                        <p class="text-base text-gray-900 break-words dark:text-gray-100">
                            {{ $message->message_title }}
                        </p>
                    </div>

                    <!-- Email Subject Card -->
                    <div class="w-full p-4 transition-all rounded-lg bg-gray-50 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Email Subject</h3>
                        <p class="text-base text-gray-900 break-words dark:text-gray-100">
                            {{ $message->email_subject }}
                        </p>
                    </div>

                    <!-- Sender Name Card -->
                    <div class="w-full p-4 transition-all rounded-lg bg-gray-50 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Sender Name</h3>
                        <p class="text-base text-gray-900 break-words dark:text-gray-100">
                            {{ $message->sender_name ?: 'Not specified' }}
                        </p>
                    </div>

                    <!-- Reply To Email Card -->
                    <div class="w-full p-4 transition-all rounded-lg bg-gray-50 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Reply To Email</h3>
                        <p class="text-base text-gray-900 break-words dark:text-gray-100">
                            {{ $message->reply_to_email ?: 'Not specified' }}
                        </p>
                    </div>

                    <!-- Plain Text Version Card -->
                    <div
                        class="w-full col-span-1 p-4 transition-all rounded-lg md:col-span-2 lg:col-span-3 bg-gray-50 dark:bg-neutral-700 hover:shadow-lg">
                        <h3 class="mb-2 text-sm text-gray-500 dark:text-gray-300">Plain Text</h3>
                        <div
                            class="w-full p-3 overflow-auto bg-white rounded-lg dark:bg-neutral-800 md:p-4">
                            <pre
                                class="font-mono text-xs text-gray-700 whitespace-pre-wrap md:text-sm dark:text-gray-300">
                                {{ $message->message_plain_text ?: 'No plain text content' }}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div
            class="w-full overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-neutral-800 md:rounded-xl dark:border-neutral-700">
            <!-- Preview Header -->
            <div
                class="flex flex-col items-start justify-between gap-2 p-4 border-b border-gray-200 md:flex-row md:items-center dark:border-neutral-700">
                <h3
                    class="text-lg font-semibold text-transparent md:text-xl bg-gradient-to-r from-gray-900 to-gray-600 dark:from-gray-100 dark:to-gray-300 bg-clip-text">
                    Email Preview
                </h3>
                <div class="flex items-center gap-5">
                    {{-- <span
                        class="px-3 py-1 text-xs text-blue-600 rounded-full bg-blue-50 dark:bg-blue-900/30 dark:text-blue-300">
                        HTML Template
                    </span> --}}

                    <x-primary-info-button href="{{ route('user.emails.message.form', $message->id) }}">
                        Edit
                    </x-primary-info-button>

                </div>
            </div>

            <!-- Preview Content -->
            <div class="w-full h-[1000px]">
                <iframe id="preview-frame" class="w-full h-full"
                    sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                    srcdoc="{{ $message->message_html }}">
                </iframe>
            </div>
        </div>
    </div>
</div>
