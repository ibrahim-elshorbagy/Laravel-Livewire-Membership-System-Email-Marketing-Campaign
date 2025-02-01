<div class="p-6 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"


x-data="{
         emailInput: '',
         parsedEmails: [],
         processing: false,
         error: null,
         textFileInput: null,
         excelFileInput: null,

         init() {
             this.textFileInput = document.getElementById('text-file-input');
             this.excelFileInput = document.getElementById('excel-file-input');

             window.addEventListener('reset-emails', () => {
                 this.emailInput = '';
                 this.parsedEmails = [];
             });
         },

         parseEmails() {
             this.parsedEmails = this.emailInput
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
         },

         validateEmail(email) {
             return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
         },

         async processTextFile() {
             this.processing = true;
             this.error = null;

             try {
                 const file = this.textFileInput.files[0];
                 if (!file) return;

                 const reader = new FileReader();
                 reader.onload = (e) => {
                     const newEmails = e.target.result
                         .split(/[\n,;]/)
                         .map(email => email.trim())
                         .filter(email => email.length > 0)
                         .join('\n');

                     this.emailInput += '\n' + newEmails;
                     this.textFileInput.value = null;
                 };
                 reader.readAsText(file);
             } catch (error) {
                 this.error = 'Error reading text file: ' + error.message;
             }
             this.processing = false;
         },

         async processExcelFile() {
             this.processing = true;
             this.error = null;

             try {
                 const file = this.excelFileInput.files[0];
                 if (!file) return;

                 if (typeof XLSX === 'undefined') {
                     throw new Error('Excel library not loaded');
                 }

                 const reader = new FileReader();
                 reader.onload = (e) => {
                     const data = new Uint8Array(e.target.result);
                     const workbook = XLSX.read(data, { type: 'array' });
                     const sheet = workbook.Sheets[workbook.SheetNames[0]];
                     const emails = XLSX.utils.sheet_to_json(sheet)
                         .flatMap(row => Object.values(row))
                         .filter(email => typeof email === 'string')
                         .map(email => email.trim())
                         .filter(email => email.length > 0)
                         .join('\n');

                     this.emailInput += '\n' + emails;
                     this.excelFileInput.value = null;
                 };
                 reader.readAsArrayBuffer(file);
             } catch (error) {
                 this.error = 'Error processing Excel: ' + error.message;
             }
             this.processing = false;
         },

         removeEmail(index) {
             this.parsedEmails.splice(index, 1);
             this.emailInput = this.parsedEmails
                 .map(e => e.value)
                 .join('\n');
         }
     }" x-effect="parseEmails()">

    <!-- Quota Display -->
    <div class="p-4 mb-4 rounded-lg bg-neutral-200 dark:bg-neutral-700">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Remaining Quota: <span class="font-bold">{{ $remainingQuota }}</span> emails
        </p>
    </div>


    <!-- File Import Section -->
    <div class="flex gap-2 mb-4">
        <!-- Text File Import -->
        <label class="inline-block px-4 py-2 text-white bg-blue-500 rounded cursor-pointer hover:bg-blue-600">
            <input type="file" id="text-file-input" class="hidden" accept=".txt,.csv" @change="processTextFile">
            Import Text File
        </label>

        <!-- Excel File Import -->
        <label class="inline-block px-4 py-2 text-white bg-green-500 rounded cursor-pointer hover:bg-green-600">
            <input type="file" id="excel-file-input" class="hidden" accept=".xls,.xlsx" @change="processExcelFile">
            Import Excel File
        </label>
    </div>

    <!-- Warning Message -->
    <div class="p-4 my-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
        <div class="flex flex-col sm:flex-row">
            <!-- Icon -->
            <div class="flex-shrink-0 sm:mr-3">
                <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <!-- Content -->
            <div class="mt-2 sm:mt-0">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    Important Note
                </h3>
                <div class="mt-2 space-y-2 text-sm text-yellow-700 dark:text-neutral-300">
                    <p class="text-xs sm:text-sm">
                        - Enter one email per line or separate emails with commas.
                    </p>
                    <p class="text-xs sm:text-sm">
                        - Press <kbd class="text-red-500">Enter</kbd> For new line.
                    </p>
                    <p class="text-xs sm:text-sm">
                        - Invalid emails will appear in <span class="text-red-500">red</span>.
                    </p>
                    <p class="text-xs sm:text-sm">
                        - You can also import emails from a text or Excel file.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    <div x-show="error" class="mb-2 text-red-500" x-text="error"></div>
    <div x-show="processing" class="mb-2 text-blue-500">Processing file...</div>

    <!-- Textarea Input -->
    <x-primary-textarea x-model="emailInput" placeholder="Enter emails separated by commas or new lines..."
        class="w-full h-64"></x-primary-textarea>

    <!-- Preview Section -->
    <div class="mt-4" x-show="parsedEmails.length > 0">
        <h3 class="mb-2 text-lg font-semibold" x-text="`Preview (${parsedEmails.length} entries)`"></h3>
        <div class="p-4 overflow-y-auto border rounded-lg max-h-96">
            <template x-for="(entry, index) in parsedEmails" :key="index">
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <span :class="{ 'text-red-500': !entry.valid }" x-text="entry.value"></span>
                    <button @click="removeEmail(index)" class="text-red-500 hover:text-red-700">
                        ✕
                    </button>
                </div>
            </template>
        </div>

        <x-primary-create-button wire:click="saveEmails(parsedEmails.filter(e => e.valid).map(e => e.value))" class="mt-4"
            x-bind:disabled="parsedEmails.filter(e => e.valid).length === 0">
            Save <span x-text="parsedEmails.filter(e => e.valid).length"></span> Valid Emails
        </x-primary-create-button>
    </div>
</div>

@push('scripts')
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
@endpush
