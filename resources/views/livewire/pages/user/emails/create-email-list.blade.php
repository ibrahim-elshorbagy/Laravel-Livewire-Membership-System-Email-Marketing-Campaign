<div class="p-6 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
    x-data="{
        emailInput: '',
        parsedEmails: [],
        allEmails: [], // New property to store all emails
        processing: false,
        error: null,
        maxDisplayEmails: 1500,
        totalEmails: 0,

        init() {
            window.addEventListener('reset-emails', () => {
                this.emailInput = '';
                this.parsedEmails = [];
                this.allEmails = [];
            });
        },

        parseEmails() {
            let emails = this.emailInput
                .split(/[\n,;]/)
                .map(email => ({
                    value: email.trim(),
                    valid: this.validateEmail(email.trim())
                }))
                .filter(entry => entry.value.length > 0)
                .reduce((acc, entry) => {



                    if (!acc.some(e => e.value === entry.value)) acc.push(entry);
                    return acc;
                }, []);


            this.totalEmails = emails.length;
            this.allEmails = emails; // Store all emails

            if (emails.length > this.maxDisplayEmails) {
                this.error = `Showing first ${this.maxDisplayEmails} of ${emails.length} emails. All valid emails will be saved.`;
                this.parsedEmails = emails.slice(0, this.maxDisplayEmails);
            } else {
                this.error = null;
                this.parsedEmails = emails;
            }
        },

        validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        async handleFileUpload(event) {
            this.processing = true;
            const file = event.target.files[0];

            if (file) {
                @this.upload('file', file, (uploadedFilename) => {
                    this.processing = false;
                    @this.processFile();
                }, () => {
                    this.processing = false;
                    this.error = 'File upload failed';
                });
            }
        }
    }" x-effect="parseEmails()">
    <!-- Header Section -->
    <div class="mb-6 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 sm:text-3xl sm:truncate">
                Add New Emails
            </h2>
        </div>
        <div class="flex mt-4 md:mt-0 md:ml-4">
            <x-primary-info-link href="{{ route('user.emails.index') }}" wire:navigate>
                Back to Mailing list
            </x-primary-info-link>
        </div>
    </div>

    <div class="p-3 my-4 bg-blue-100 rounded-lg dark:bg-blue-900">
        <ul class="pl-5 text-sm list-disc text-gray-700 dark:text-gray-200">
            <li>
                <i class="mr-2 text-blue-600 fas fa-envelope dark:text-blue-300"></i>
                Remaining Quota: <span class="font-bold">{{ $remainingQuota }}</span> emails
            </li>
        </ul>
    </div>
    <!-- Warning Message -->
    <div class="p-4 my-4 bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
        <div class="flex flex-col">
            <!-- Icon -->
            <div class="flex gap-4 items-center">
                <div class="flex-shrink-0 sm:mr-3">
                    <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">
                    Important Note
                </h3>
            </div>
            <!-- Content -->
            <div class="mt-2 sm:mt-0">
                <div class="mt-2 space-y-2 text-yellow-700 text-md dark:text-neutral-300">
                    <p >
                        - Importing will start immediately when importing from  ( file text or Excel file )
                    </p>
                    <p>
                        - You Can Import Email And Name with Excel file only
                    </p>
                    <p >
                        - Enter one email per line or separate emails with commas.
                    </p>
                    <p >
                        - Press <kbd class="text-red-500">Enter</kbd> For new line.
                    </p>
                    <p >
                        - Invalid emails will appear in <span class="text-red-500">red</span>.
                    </p>
                    <p >
                        - Maximum displayed: <span class="font-bold" x-text="maxDisplayEmails"></span> emails
                    </p>
                    <p >
                        - Example of accepted text file format:
                    </p>
                    <p class="my-4 text-sm text-gray-600 dark:text-gray-300">
                        test1@outlook.com -> test1@outlook.com <br>
                        1. test@outlook.com -> test@outlook.com <br>
                        2- test@outlook.com -> test@outlook.com <br>
                        3: test@example.com -> test@example.com <br>
                        4) test1@email.com -> test1@email.com <br>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- List Selection -->


    <div class="grid grid-cols-1 gap-4 items-center mb-4 sm:grid-cols-2 justify-normal">
        <div>
            <x-input-label for="list_id">Select List</x-input-label>
            <x-primary-select-input wire:model.live="list_id" id="list_id"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="0">Select a list</option>
                @foreach($emailLists as $list)
                <option value="{{ $list->id }}">{{ $list->name }}</option>
                @endforeach
            </x-primary-select-input>
            <x-input-error :messages="$errors->get('list_id')" class="mt-2" />
        </div>


    </div>

    <!-- File Import Section -->
    @if($list_id != 0)
    <div class="flex gap-2 mb-4 text-sm md:text-md">
        <label class="inline-block px-4 py-2 text-white bg-blue-500 rounded cursor-pointer hover:bg-blue-600">
            <input type="file" wire:model="file" @change="handleFileUpload" class="hidden"
                accept=".txt,.csv,.xls,.xlsx">
            Import File
        </label>
    </div>
    @else
    <div  class="flex gap-2 mb-4 text-sm md:text-md">
        <label class="inline-block px-4 py-2 text-white bg-gray-700 rounded cursor-not-allowed hover:bg-gray-700">
            Import File (Select List First)
        </label>
    </div>
    @endif

    <!-- Processing Indicator -->
    <div x-show="processing" class="p-4 mb-4 text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-900 dark:text-blue-300">
        <div class="flex items-center">
            <svg class="mr-2 w-5 h-5 animate-spin" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            Uploading file...
        </div>
    </div>

    <!-- Error Messages -->
    <div x-show="error"
        class="p-4 mb-4 text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-900 dark:text-yellow-300"
        x-text="error"></div>

    <!-- Manual Input Section -->
    <x-primary-textarea x-model="emailInput" placeholder="Enter emails separated by commas or new lines..."
        class="w-full h-64">
    </x-primary-textarea>

    <!-- Preview Section -->
    <div class="mt-4" x-show="parsedEmails.length > 0">
        <h3 class="mb-2 text-lg font-semibold" x-text="`Preview (${parsedEmails.length} entries)`"></h3>
        <div class="overflow-y-auto p-4 max-h-96 rounded-lg border">
            <template x-for="(entry, index) in parsedEmails" :key="index">
                <div class="flex gap-2 justify-between items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span :class="{ 'text-red-500': !entry.valid }" x-text="entry.value"></span>
                </div>
            </template>
        </div>

        <!-- Save Button -->
        <x-primary-create-button wire:click="saveEmails(allEmails.filter(e => e.valid).map(e => e.value))" class="mt-4"
            x-bind:disabled="allEmails.filter(e => e.valid).length === 0">
            Save <span x-text="allEmails.filter(e => e.valid).length"></span> Valid Emails
        </x-primary-create-button>
    </div>
</div>


