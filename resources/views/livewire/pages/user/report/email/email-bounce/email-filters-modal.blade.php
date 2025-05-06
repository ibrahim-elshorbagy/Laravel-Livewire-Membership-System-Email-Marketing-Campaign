<div>
    <x-modal name="add-emails-modal" maxWidth="xl">
        <div class="p-6" x-data="emailBounceModal()">

            <h2 class="text-lg font-medium">Add Email Filters</h2>

            <!-- Instructions with Filter Explanation -->
            <div
                class="p-3 mt-4 mb-4 text-sm text-yellow-800 bg-yellow-50 rounded-lg dark:bg-yellow-900/20 dark:text-yellow-200">
                <p class="mb-2"><strong>Email Filter System:</strong></p>
                <ul class="pl-5 list-disc">
                    <li>You can enter partial email patterns like:</li>
                    <ul class="pl-5 list-circle">
                        <li><strong>support@</strong> - Will match all emails starting with "support@"</li>
                        <li><strong>@example.com</strong> - Will match all emails with this domain</li>
                        <li><strong>info</strong> - Will match all emails containing "info" anywhere</li>
                    </ul>
                    <li>Set each pattern as "Hard" or "Soft" bounce type</li>
                    <li>Hard bounces will immediately flag matching emails</li>
                    <li>Soft bounces increment a counter that may eventually become a hard bounce</li>
                </ul>
            </div>

            <!-- Error Message -->
            <div x-show="error" x-text="error"
                class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900 dark:text-red-200">
            </div>

            <!-- Textarea for Email Input -->
            <x-primary-textarea x-model="emailInput" x-on:input="parseEmails()"
                placeholder="Enter email filter patterns separated by commas, semicolons or new lines..." class="w-full h-40">
            </x-primary-textarea>

            <!-- Email Preview with Type Selection -->
            <div class="mt-4" x-show="parsedEmails.length > 0">
                <h3 class="mb-2 text-sm font-semibold" x-text="`Preview (${parsedEmails.length} patterns)`">
                </h3>

                <div class="overflow-y-auto p-2 max-h-64 rounded-lg border border-gray-200 dark:border-gray-700">
                    <template x-for="(entry, index) in parsedEmails" :key="index">
                        <div class="flex justify-between items-center p-1 text-sm">
                            <span x-text="entry.value" class="mr-2">
                            </span>

                            <select x-model="entry.type" @change="updateEmailType(index, $event.target.value)"
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

                    <x-primary-create-button x-on:click="$wire.saveBounceEmails(getEmailData())">
                        <div class="flex gap-2">
                            Add <span x-text="allEmails.length"></span> Filters
                        </div>
                    </x-primary-create-button>
                </div>
            </div>

            <!-- Empty State -->
            <div class="mt-4 text-center text-gray-500 dark:text-gray-400" x-show="parsedEmails.length === 0">
                No email filters entered yet. Start typing or paste email patterns above.
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
                        this.error = `Showing first ${this.maxDisplayEmails} of ${emails.length} patterns. All patterns will be saved.`;
                        this.parsedEmails = emails.slice(0, this.maxDisplayEmails);
                    } else {
                        this.error = null;
                        this.parsedEmails = emails;
                    }
                },

                getEmailData() {
                    return this.allEmails.map(e => ({
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
                }
            };
    }
</script>
@endpush
