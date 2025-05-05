<div>
    <x-modal name="add-emails-modal" maxWidth="xl">
        <div class="p-6" x-data="emailBounceModal()">

            <h2 class="text-lg font-medium">Add Bounced Emails</h2>

            <!-- Instructions -->
            <div
                class="p-3 mt-4 mb-4 text-sm text-yellow-800 bg-yellow-50 rounded-lg dark:bg-yellow-900/20 dark:text-yellow-200">
                <p class="mb-2"><strong>Instructions:</strong></p>
                <ul class="pl-5 list-disc">
                    <li>Enter one email per line or separate with commas/semicolons</li>
                    <li>Invalid emails will appear in <span class="text-red-500">red</span></li>
                    <li>Set bounce type for each email using the dropdown</li>
                    <li>Maximum Per Try: <span x-text="maxDisplayEmails"></span> emails</li>
                </ul>
            </div>

            <!-- Error Message -->
            <div x-show="error" x-text="error"
                class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900 dark:text-red-200">
            </div>

            <!-- Textarea for Email Input -->
            <x-primary-textarea x-model="emailInput" x-on:input="parseEmails()"
                placeholder="Enter emails separated by commas, semicolons or new lines..." class="w-full h-40">
            </x-primary-textarea>

            <!-- Email Preview with Type Selection -->
            <div class="mt-4" x-show="parsedEmails.length > 0">
                <h3 class="mb-2 text-sm font-semibold"
                    x-text="`Preview (${parsedEmails.length} emails - ${allEmails.filter(e => e.valid).length} valid)`">
                </h3>

                <div class="overflow-y-auto p-2 max-h-64 rounded-lg border border-gray-200 dark:border-gray-700">
                    <template x-for="(entry, index) in parsedEmails" :key="index">
                        <div
                            class="flex justify-between items-center p-1 text-sm">
                            <span :class="{ 'text-red-500': !entry.valid }" x-text="entry.value" class="mr-2">
                            </span>

                            <select x-model="entry.type" @change="updateEmailType(index, $event.target.value)"
                                :disabled="!entry.valid"
                                class="px-4 py-2 w-32 text-sm rounded-lg appearance-none bg-neutral-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black disabled:cursor-not-allowed disabled:opacity-75 dark:bg-neutral-800/50 dark:focus-visible:outline-orange-500 dark:text-neutral-200">
                                <option value="soft">Soft</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </template>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end mt-4 space-x-3">
                    <x-secondary-button x-on:click="resetForm()">
                        Clear
                    </x-secondary-button>

                    <x-primary-create-button
                        x-on:click="if(validateBeforeSubmit()) { $wire.saveBounceEmails(getValidEmailData()) }"
                        x-bind:disabled="allEmails.filter(e => e.valid).length === 0">
                        <div class="flex gap-2">
                            Add <span x-text="allEmails.filter(e => e.valid).length"></span> Emails
                        </div>
                    </x-primary-create-button>
                </div>
            </div>

            <!-- Empty State -->
            <div class="mt-4 text-center text-gray-500 dark:text-gray-400" x-show="parsedEmails.length === 0">
                No emails entered yet. Start typing or paste emails above.
            </div>
        </div>
    </x-modal>
</div>


@push('scripts')
<script>
    function emailBounceModal() {
            return {
                emailInput: '',
                parsedEmails: [],
                allEmails: [],
                error: null,
                maxDisplayEmails: 1500,
                totalEmails: 0,

                resetForm() {
                    this.emailInput = '';
                    this.parsedEmails = [];
                    this.allEmails = [];
                    this.error = null;
                },

                parseEmails() {
                    let emails = this.emailInput
                        .split(/[\n,;]/)
                        .map(email => ({
                            value: email.trim(),
                            valid: this.validateEmail(email.trim()),
                            type: 'soft'
                        }))
                        .filter(entry => entry.value.length > 0)
                        .reduce((acc, entry) => {
                            if (!acc.some(e => e.value === entry.value)) acc.push(entry);
                            return acc;
                        }, []);

                    this.totalEmails = emails.length;
                    this.allEmails = emails;

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

                getValidEmailData() {
                    return this.allEmails
                        .filter(e => e.valid)
                        .map(e => ({
                            email: e.value,
                            type: e.type
                        }));
                },

                updateEmailType(index, type) {
                    this.parsedEmails[index].type = type;
                    const emailValue = this.parsedEmails[index].value;
                    const allEmailIndex = this.allEmails.findIndex(e => e.value === emailValue);
                    if (allEmailIndex !== -1) {
                        this.allEmails[allEmailIndex].type = type;
                    }
                },

                validateBeforeSubmit() {
                    const validEmails = this.allEmails.filter(e => e.valid);
                    if (validEmails.length === 0) {
                        this.error = 'Please enter at least one valid email';
                        return false;
                    }
                    return true;
                }
            };
    }
</script>
@endpush
