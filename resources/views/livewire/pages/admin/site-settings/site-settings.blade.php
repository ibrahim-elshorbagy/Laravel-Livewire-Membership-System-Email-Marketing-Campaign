<div class="container mx-auto">
    <div class="p-3 bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
            Site Settings
        </h2>

        <form wire:submit.prevent="updateSiteSettings" class="space-y-6">
            <div class="grid gap-6 p-4 rounded-lg border md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <div>
                    <x-input-label for="site_name" :value="__('Site Name')" />
                    <x-text-input wire:model="site_name" id="site_name" type="text" class="block mt-1 w-full"
                        required />
                    <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="support_phone" :value="__('Support Phone')" />
                    <x-text-input wire:model="support_phone" id="support_phone" type="text" class="block mt-1 w-full"
                        required />
                    <x-input-error :messages="$errors->get('support_phone')" class="mt-2" />
                </div>
            </div>


            <div class="grid gap-6 p-4 rounded-lg border md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <div>
                    <x-input-label for="auth_image" :value="__('Auth Background Image')" />
                    <x-primary-upload-button wire:model="new_auth_image" id="auth_image" type="file" accept="image/*"
                        class="block mt-1 w-full" />

                    <div class="flex items-center mt-2 space-x-4">
                        @if($auth_image_preview)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">New Auth Image
                                Preview</span>
                            <img src="{{ $auth_image_preview }}" alt="New Auth Image Preview"
                                class="w-auto h-20 rounded border dark:border-neutral-600">
                        </div>
                        @endif

                        @if($auth_image)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">Current Auth Image</span>
                            <img src="{{ Storage::url($auth_image) }}" alt="Current Auth Image"
                                class="w-auto h-20 rounded border dark:border-neutral-600">
                        </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('new_auth_image')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="logo" :value="__('Site Logo')" />
                    <x-primary-upload-button wire:model="new_logo" id="logo" type="file" accept="image/*"
                        class="block mt-1 w-full" />

                    <div class="flex items-center mt-2 space-x-4">
                        @if($logo_preview)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">New Logo Preview</span>
                            <img src="{{ $logo_preview }}" alt="New Logo Preview"
                                class="w-auto h-20 rounded border dark:border-neutral-600">
                        </div>
                        @endif

                        @if($logo)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">Current Logo</span>
                            <img src="{{ Storage::url($logo) }}" alt="Current Logo"
                                class="w-auto h-20 rounded border dark:border-neutral-600">
                        </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('new_logo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="favicon" :value="__('Site Favicon')" />
                    <x-primary-upload-button wire:model="new_favicon" id="favicon" type="file" accept="image/*"
                        class="block mt-1 w-full" />

                    <div class="flex items-center mt-2 space-x-4">
                        @if($favicon_preview)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">New Favicon Preview</span>
                            <img src="{{ $favicon_preview }}" alt="New Favicon Preview"
                                class="w-16 h-16 rounded border dark:border-neutral-600">
                        </div>
                        @endif

                        @if($favicon)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">Current Favicon</span>
                            <img src="{{ Storage::url($favicon) }}" alt="Current Favicon"
                                class="w-16 h-16 rounded border dark:border-neutral-600">
                        </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('new_favicon')" class="mt-2" />
                </div>
            </div>

            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    SEO Settings
                </h3>

                <div class="grid gap-6 md:grid-cols-1">

                    <div>
                        <x-input-label for="meta_description" :value="__('Meta Description')" />
                        <textarea wire:model="meta_description" id="meta_description"
                            class="block mt-1 w-full dark:bg-neutral-900/50 dark:text-white"
                            placeholder="Enter meta description" rows="3">
                        </textarea>

                        <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="meta_keywords" :value="__('Meta Keywords')" />
                        <x-text-input wire:model="meta_keywords" id="meta_keywords" type="text"
                            class="block mt-1 w-full" placeholder="Enter meta keywords (comma-separated)" />
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Separate keywords with commas
                        </p>
                        <x-input-error :messages="$errors->get('meta_keywords')" class="mt-2" />
                    </div>
                </div>

            </div>
            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Time Settings
                </h3>
                <div class="grid gap-6 md:grid-cols-1">
                    <div>
                        <x-input-label for="APP_TIMEZONE" :value="__('Default Timezone')" />
                        <x-primary-select-input wire:model="APP_TIMEZONE" id="APP_TIMEZONE"
                            class="block mt-1 w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-900/50 dark:text-white focus:border-sky-500 focus:ring-sky-500">
                            @foreach(timezone_identifiers_list() as $timezone)
                            <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </x-primary-select-input>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            This will be the default timezone for all new users
                        </p>
                        <x-input-error :messages="$errors->get('APP_TIMEZONE')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- PLAN SETTINGS BOX -->
            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Plans Settings
                </h3>

                <div class="grid gap-6 md:grid-cols-1">
                    <div>
                        <!-- Label for the Grace Days input -->
                        <x-input-label for="grace_days" :value="__('Grace Days')" />

                        <!-- Input for setting grace period after plan expiration -->
                        <x-text-input wire:model="grace_days" id="grace_days" type="number" min="0"
                            class="block mt-1 w-full" required />

                        <!-- Description of what Grace Days means -->
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Number of extra days a user can keep using his plan add emails or sending it
                            <br>
                            After That his Subscription will be Suppress and API won't retive his emails
                            <br>
                        </p>

                        <!-- Display validation errors for grace_days -->
                        <x-input-error :messages="$errors->get('grace_days')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Content Size Limits
                </h3>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="base64_image_size_limit" :value="__('Base64 Image Size Limit (KB)')" />
                        <x-text-input wire:model="base64_image_size_limit" id="base64_image_size_limit" type="number"
                            min="1" max="16000" class="block mt-1 w-full" required />
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Maximum size allowed for encoded images in content (in kilobytes)
                        </p>
                        <x-input-error :messages="$errors->get('base64_image_size_limit')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="html_size_limit" :value="__('HTML Content Size Limit (KB)')" />
                        <x-text-input wire:model="html_size_limit" id="html_size_limit" type="number" min="1"
                            max="16000" class="block mt-1 w-full" required />
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Maximum size allowed for HTML content (in kilobytes) - Max Is 16000 KB
                        </p>
                        <x-input-error :messages="$errors->get('html_size_limit')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- SITE ACCESS BOX -->
            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Site Access
                </h3>

                <!-- Two toggle switches in a row (or column on mobile) -->
                <div class="flex flex-col gap-5 md:flex-row">

                    <!-- Maintenance Mode Toggle -->
                    <div>
                        <label for="maintenance"
                            class="inline-flex gap-3 justify-between items-center px-4 py-1.5 rounded-lg min-w-52 bg-neutral-100 dark:bg-neutral-800">

                            <input id="maintenance" wire:model="maintenance" type="checkbox" class="sr-only peer"
                                role="switch" />
                            <span
                                class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Maintenance
                                Mode</span>

                            <!-- Visual part of the switch -->
                            <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full bg-white after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-950 dark:after:bg-neutral-400 dark:peer-checked:bg-orange-500 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-orange-500"
                                aria-hidden="true"></div>

                        </label>

                        <!-- Validation error if exists -->
                        <x-input-error :messages="$errors->get('maintenance')" class="mt-2" />

                        <!-- Explanation for Maintenance Mode -->
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            If enabled, the <strong>Emails API</strong> will be shut down temporarily.And External
                            Servers won't be able to
                            retrieve emails during this period.
                        </p>
                    </div>

                    <!-- Only Our Devices Toggle -->
                    <div>
                        <label for="our_devices"
                            class="inline-flex gap-3 justify-between items-center px-4 py-1.5 rounded-lg min-w-52 bg-neutral-100 dark:bg-neutral-800">

                            <input id="our_devices" wire:model="our_devices" type="checkbox" class="sr-only peer"
                                role="switch" />
                            <span
                                class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Only
                                Our Devices</span>

                            <!-- Visual part of the switch -->
                            <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full bg-white after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-950 dark:after:bg-neutral-400 dark:peer-checked:bg-orange-500 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-orange-500"
                                aria-hidden="true"></div>

                        </label>

                        <!-- Validation error if exists -->
                        <x-input-error :messages="$errors->get('our_devices')" class="mt-2" />

                        <!-- Explanation for Only Our Devices -->
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            If enabled, only requests from <strong>Our External Servers</strong> are allowed to access
                            the Emails API.
                            <br>
                            If disabled, anyone who has the correct API Password can use a browser or any tool (like
                            Postman) to
                            access the API.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subscription Notification Settings -->
            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Subscription Notification Settings
                </h3>
                <p class="my-2 text-md text-neutral-600 dark:text-neutral-400">
                    Notification sent to users before their subscription expires.
                </p>
                <div class="grid gap-6 md:grid-cols-1">
                    <div>
                        <x-input-label for="subscription_notify_days" :value="__('Notification Days Before Expiry')" />
                        <x-text-input wire:model="subscription_notify_days" id="subscription_notify_days" type="number"
                            min="1" class="block mt-1 w-full" required />
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Number of days before subscription expiry to send notification
                        </p>
                        <x-input-error :messages="$errors->get('subscription_notify_days')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="subscription_notify_title" :value="__('Notification Title')" />
                        <x-text-input wire:model="subscription_notify_title" id="subscription_notify_title" type="text"
                            class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('subscription_notify_title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="subscription_notify_message" :value="__('Notification Message')" />
                        <x-primary-textarea wire:model="subscription_notify_message" id="subscription_notify_message"
                            class="block mt-1 w-full dark:bg-neutral-900/50 dark:text-white" rows="3"
                            required></x-primary-textarea>
                        <x-input-error :messages="$errors->get('subscription_notify_message')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Mail Settings
                </h3>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="mail_mailer" :value="__('Mail Driver')" />
                        <x-primary-select-input wire:model="mail_mailer" id="mail_mailer" class="block mt-1 w-full">
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="log">Log</option>
                        </x-primary-select-input>
                        <x-input-error :messages="$errors->get('mail_mailer')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mail_host" :value="__('SMTP Host')" />
                        <x-text-input wire:model="mail_host" id="mail_host" type="text" class="block mt-1 w-full"
                            required />
                        <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mail_port" :value="__('SMTP Port')" />
                        <x-text-input wire:model="mail_port" id="mail_port" type="number" class="block mt-1 w-full"
                            required />
                        <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mail_username" :value="__('SMTP Username')" />
                        <x-text-input wire:model="mail_username" id="mail_username" type="text"
                            class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mail_password" :value="__('SMTP Password')" />
                        <x-text-input wire:model="mail_password" id="mail_password" class="block mt-1 w-full"
                            required />
                        <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="mail_from_address" :value="__('From Support Email')" />
                        <x-text-input wire:model="mail_from_address" id="mail_from_address" type="email"
                            class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('mail_from_address')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mail_from_name" :value="__('From Name')" />
                        <x-text-input wire:model="mail_from_name" id="mail_from_name" type="text"
                            class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Footer Settings
                </h3>

                <div class="grid gap-6 md:grid-cols-1">

                    <div>
                        <x-input-label for="footer_first_line" :value="__('First Line')" />
                        <x-text-input wire:model="footer_first_line" id="footer_first_line" type="text"
                            class="block mt-1 w-full" placeholder="Enter footer first line" />
                        <x-input-error :messages="$errors->get('footer_first_line')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="footer_second_line" :value="__('Second Line')" />
                        <x-text-input wire:model="footer_second_line" id="footer_second_line" type="text"
                            class="block mt-1 w-full" placeholder="Enter footer second line" />
                        <x-input-error :messages="$errors->get('footer_second_line')" class="mt-2" />
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <x-primary-create-button type="submit">
                    Update Site Settings
                </x-primary-create-button>
            </div>
        </form>



        <div class="p-6 my-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                Bounce Pattern Settings
            </h3>


            <div class="grid gap-6 md:grid-cols-1">

                <div class="mt-4 space-y-4">
                    <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">Add New Pattern</h4>
                    <div class="w-48">
                        <x-input-label for="newPatternType" :value="__('Pattern Type')" />
                        <x-primary-select-input wire:model="newPatternType" id="newPatternType"
                            class="block mt-1 w-full">
                            <option value="">Select Type</option>
                            <option value="subject">Subject</option>
                            <option value="hard">Hard Bounce</option>
                            <option value="soft">Soft Bounce</option>
                        </x-primary-select-input>
                        <x-input-error :messages="$errors->get('newPatternType')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="newPattern" :value="__('Pattern Text')" />
                        <x-textarea-input wire:model="newPattern" id="newPattern"
                            placeholder="Enter patterns (one per line)" class="block mt-1 w-full" />
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Enter each pattern on a new line
                        </div>
                        <x-input-error :messages="$errors->get('newPattern')" class="mt-2" />
                    </div>

                    <div class="flex items-end">
                        <x-primary-create-button wire:click="addBouncePattern" type="button" class="mb-1">
                            Add Pattern
                        </x-primary-create-button>
                    </div>
                </div>

                <x-primary-accordion title="Patterns" :isExpandedByDefault="false">
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <x-text-input wire:model.live="searchPattern" type="search" placeholder="Search patterns..."
                                class="w-full sm:w-48" />
                            <x-primary-select-input wire:model.live="filterType" id="filterType" class="w-full sm:w-48">
                                <option value="">All</option>
                                <option value="subject">subject</option>
                                <option value="hard">hard</option>
                                <option value="soft">soft</option>
                            </x-primary-select-input>

                            <x-primary-select-input wire:model.live="sortField" class="w-full sm:w-48">
                                <option value="pattern">pattern</option>
                                <option value="type">type</option>
                                <option value="created_at">Added Date</option>
                            </x-primary-select-input>

                            <x-primary-select-input wire:model.live="sortDirection" class="w-full sm:w-32">
                                <option value="asc">asc</option>
                                <option value="desc">desc</option>
                            </x-primary-select-input>

                            <x-primary-select-input wire:model.live="perPage" class="w-full sm:w-32">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </x-primary-select-input>
                        </div>
                    </div>
                    <div class="overflow-x-auto w-full rounded-lg">
                        <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
                            <thead
                                class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
                                <tr>
                                    <th scope="col" class="p-4">Type</th>
                                    <th scope="col" class="p-4">Pattern</th>
                                    <th scope="col" class="p-4">Added Date</th>
                                    <th scope="col" class="p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
                                @forelse($bouncePatterns as $pattern)
                                <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                    wire:key="pattern-row-{{ $pattern->id }}">
                                    <td class="p-4">{{ ucfirst($pattern->type) }}</td>
                                    <td class="p-4">{{ $pattern->pattern }}</td>
                                    <td class="p-4">{{ $pattern->created_at }}</td>
                                    <td class="p-4">
                                        <div class="flex gap-2">
                                            <button type="button"
                                                class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                                x-on:click="$dispatch('open-modal', 'edit-pattern-modal'); $wire.selectedPatternId = {{ $pattern->id }}; $wire.editPatternType = '{{ $pattern->type }}'; $wire.editPattern = '{{ $pattern->pattern }}'">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" wire:click="deletePattern({{ $pattern->id }})"
                                                wire:confirm="Are you sure you want to delete this pattern?"
                                                class="text-neutral-400 hover:text-red-600 dark:hover:text-red-500">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-neutral-500 dark:text-neutral-400">
                                        No bounce patterns found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $bouncePatterns->links(data: ['scrollTo' => false]) }}
                        </div>
                    </div>
                </x-primary-accordion>

                <!-- Edit Pattern Modal -->
                <x-modal name="edit-pattern-modal" maxWidth="md">
                    <div class="p-6">
                        <h2 class="text-lg font-medium">Edit Pattern</h2>
                        <form wire:submit.prevent="updatePattern" class="mt-4">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="patternType" value="Pattern Type" />
                                    <x-primary-select-input wire:model="editPatternType" id="patternType"
                                        class="block mt-1 w-full">
                                        <option value="subject">Subject</option>
                                        <option value="hard">Hard Bounce</option>
                                        <option value="soft">Soft Bounce</option>
                                    </x-primary-select-input>
                                    <x-input-error :messages="$errors->get('editPatternType')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="pattern" value="Pattern" />
                                    <x-text-input wire:model="editPattern" id="pattern" class="block mt-1 w-full" />
                                    <x-input-error :messages="$errors->get('editPattern')" class="mt-2" />
                                </div>
                            </div>
                            <div class="flex justify-end mt-6 space-x-3">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-pattern-modal')">
                                    Cancel
                                </x-secondary-button>
                                <x-primary-create-button type="submit">
                                    Update
                                </x-primary-create-button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            </div>
        </div>



    </div>
</div>
