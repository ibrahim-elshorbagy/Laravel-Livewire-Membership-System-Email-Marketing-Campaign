<div class="container mx-auto">
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-neutral-800">
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
                    <x-input-label for="support_email" :value="__('Support Email')" />
                    <x-text-input wire:model="support_email" id="support_email" type="email" class="block mt-1 w-full"
                        required />
                    <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
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

            <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Site Access
                </h3>
                <div class="flex flex-col gap-5 md:flex-row">
                    <div>
                        <label for="maintenance" class="inline-flex gap-3 justify-between items-center px-4 py-1.5 rounded-lg min-w-52 bg-neutral-100 dark:bg-neutral-800">

                            <input id="maintenance" wire:model="maintenance" type="checkbox" class="sr-only peer" role="switch" />
                            <span class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Maintenance Mode</span>

                            <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full bg-white after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-950 dark:after:bg-neutral-400 dark:peer-checked:bg-orange-500 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-orange-500" aria-hidden="true"></div>

                        </label>
                        <x-input-error :messages="$errors->get('maintenance')" class="mt-2" />
                    </div>
                    <div>
                        <label for="our_devices" class="inline-flex gap-3 justify-between items-center px-4 py-1.5 rounded-lg min-w-52 bg-neutral-100 dark:bg-neutral-800">

                            <input id="our_devices" wire:model="our_devices" type="checkbox" class="sr-only peer" role="switch" />
                            <span class="text-xs font-medium md:text-sm trancking-wide text-neutral-600 peer-checked:text-neutral-900 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:text-neutral-400 dark:peer-checked:text-neutral-100">Only Our Devices</span>

                                <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full bg-white after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-neutral-600 after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:bg-neutral-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-neutral-800 peer-focus:peer-checked:outline-black peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 dark:border-neutral-700 dark:bg-neutral-950 dark:after:bg-neutral-400 dark:peer-checked:bg-orange-500 dark:peer-checked:after:bg-black dark:peer-focus:outline-neutral-300 dark:peer-focus:peer-checked:outline-orange-500" aria-hidden="true"></div>

                        </label>
                        <x-input-error :messages="$errors->get('our_devices')" class="mt-2" />
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
    </div>
</div>