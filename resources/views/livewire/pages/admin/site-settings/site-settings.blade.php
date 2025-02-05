<div class="container p-6 mx-auto">
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
            Site Settings
        </h2>

        <form wire:submit.prevent="updateSiteSettings" class="space-y-6">
            <div class="grid gap-6 p-4 border rounded-lg md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <div>
                    <x-input-label for="site_name" :value="__('Site Name')" />
                    <x-text-input wire:model="site_name" id="site_name" type="text" class="block w-full mt-1"
                        required />
                    <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="support_email" :value="__('Support Email')" />
                    <x-text-input wire:model="support_email" id="support_email" type="email" class="block w-full mt-1"
                        required />
                    <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="support_phone" :value="__('Support Phone')" />
                    <x-text-input wire:model="support_phone" id="support_phone" type="text" class="block w-full mt-1" required />
                    <x-input-error :messages="$errors->get('support_phone')" class="mt-2" />
                </div>
            </div>


            <div class="grid gap-6 p-4 border rounded-lg md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <div>
                    <x-input-label for="logo" :value="__('Site Logo')" />
                    <x-primary-upload-button wire:model="new_logo" id="logo" type="file" accept="image/*"
                        class="block w-full mt-1" />

                    <div class="flex items-center mt-2 space-x-4">
                        @if($logo_preview)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">New Logo Preview</span>
                            <img src="{{ $logo_preview }}" alt="New Logo Preview"
                                class="w-auto h-20 border rounded dark:border-neutral-600">
                        </div>
                        @endif

                        @if($logo)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">Current Logo</span>
                            <img src="{{ Storage::url($logo) }}" alt="Current Logo"
                                class="w-auto h-20 border rounded dark:border-neutral-600">
                        </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('new_logo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="favicon" :value="__('Site Favicon')" />
                    <x-primary-upload-button wire:model="new_favicon" id="favicon" type="file" accept="image/*"
                        class="block w-full mt-1" />

                    <div class="flex items-center mt-2 space-x-4">
                        @if($favicon_preview)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">New Favicon Preview</span>
                            <img src="{{ $favicon_preview }}" alt="New Favicon Preview"
                                class="w-16 h-16 border rounded dark:border-neutral-600">
                        </div>
                        @endif

                        @if($favicon)
                        <div class="flex flex-col items-center">
                            <span class="mb-2 text-sm text-neutral-600 dark:text-neutral-300">Current Favicon</span>
                            <img src="{{ Storage::url($favicon) }}" alt="Current Favicon"
                                class="w-16 h-16 border rounded dark:border-neutral-600">
                        </div>
                        @endif
                    </div>

                    <x-input-error :messages="$errors->get('new_favicon')" class="mt-2" />
                </div>
            </div>

            <div class="p-6 border rounded-lg border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    SEO Settings
                </h3>

                <div class="grid gap-6 md:grid-cols-1">

                    <div>
                        <x-input-label for="meta_description" :value="__('Meta Description')" />
                        <textarea wire:model="meta_description" id="meta_description" class="block w-full mt-1 dark:bg-neutral-900/50 dark:text-white"
                            placeholder="Enter meta description" rows="3" >
                        </textarea>

                        <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="meta_keywords" :value="__('Meta Keywords')" />
                        <x-text-input wire:model="meta_keywords" id="meta_keywords" type="text" class="block w-full mt-1"
                            placeholder="Enter meta keywords (comma-separated)" />
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                            Separate keywords with commas
                        </p>
                        <x-input-error :messages="$errors->get('meta_keywords')" class="mt-2" />
                    </div>
                </div>

            </div>
            <div class="p-6 border rounded-lg border-neutral-200 dark:border-neutral-600">
                <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
                    Footer Settings
                </h3>

                <div class="grid gap-6 md:grid-cols-1">

                    <div>
                        <x-input-label for="footer_first_line" :value="__('First Line')" />
                        <x-text-input wire:model="footer_first_line" id="footer_first_line" type="text" class="block w-full mt-1"
                            placeholder="Enter footer first line" />
                        <x-input-error :messages="$errors->get('footer_first_line')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="footer_second_line" :value="__('Second Line')" />
                        <x-text-input wire:model="footer_second_line" id="footer_second_line" type="text" class="block w-full mt-1"
                            placeholder="Enter footer second line" />
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
