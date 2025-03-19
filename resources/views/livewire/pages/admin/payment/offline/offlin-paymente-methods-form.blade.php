<div
    class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $method_id ? 'Edit Payment Method' : 'New Payment Method' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('admin.offline-payment-methods') }}" wire:navigate>
                Back To Payment Methods
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="savePaymentMethod" class="space-y-4">
        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <x-input-label for="name" required>Method Name</x-input-label>
                <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" required>Slug</x-input-label>
                <x-text-input wire:model="slug" id="slug" type="text" class="block mt-1 w-full" required />
                <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                    Note: The slug is used throughout the system. Please do not change it once set.
                </div>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>


            <div class="lg:col-span-2">
                <x-input-label for="instructions" required>Instructions</x-input-label>
                <div wire:ignore>
                    <textarea id="instructions" class="block mt-1 w-full"></textarea>
                </div>
                <input type="hidden" wire:model="instructions">
                <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
            </div>

            <div >
                <x-input-label for="logo" :value="__('Method Logo')" />
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
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <x-input-label for="receipt_image">Receipt Image Required</x-input-label>
                    <label class="inline-flex relative items-center mt-3 cursor-pointer">
                        <input type="checkbox" wire:model="receipt_image" class="sr-only peer" checked>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                        </div>
                    </label>
                    <x-input-error :messages="$errors->get('receipt_image')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="active">Active</x-input-label>
                    <label class="inline-flex relative items-center mt-3 cursor-pointer">
                        <input type="checkbox" wire:model="active" class="sr-only peer" checked>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                        </div>
                    </label>
                    <x-input-error :messages="$errors->get('active')" class="mt-2" />
            </div>
            </div>

        </div>

        <div class="flex justify-end mt-6">
            <x-primary-create-button type="submit">
                {{ $method_id ? 'Update Payment Method' : 'Create Payment Method' }}
            </x-primary-create-button>
        </div>
    </form>
</div>


@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('livewire:initialized', function () {
        let editor;

        ClassicEditor
            .create(document.querySelector('#instructions'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'insertTable', 'imageUpload', 'undo', 'redo'],
                image: {
                    upload: {
                        types: ['jpeg', 'png', 'gif', 'jpg', 'webp']
                    }
                }
            })
            .then(newEditor => {
                editor = newEditor;

                // Set initial data if it exists
                if (@this.instructions) {
                    editor.setData(@this.instructions);
                }

                // Update Livewire model when content changes
                editor.model.document.on('change:data', () => {
                    @this.set('instructions', editor.getData());
                });

                // Handle file uploads using Livewire component method
                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    return {
                        upload: async () => {
                            const file = await loader.file;

                            // Convert file to base64
                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = async () => {
                                    // Send the base64 data to the Livewire component
                                    const fileData = reader.result;

                                    try {
                                        const result = await @this.uploadCKEditorImage(fileData);

                                        if (result.success) {
                                            resolve({
                                                default: result.url
                                            });
                                        } else {
                                            reject(result.error);
                                        }
                                    } catch (error) {
                                        reject('Upload failed');
                                    }
                                };
                                reader.onerror = () => reject('Failed to read file');
                            });
                        },
                        abort: () => {}
                    };
                };
            })
            .catch(error => console.error(error));

        // Clean up on component disconnect
        Livewire.on('disconnected', () => {
            if (editor) {
                editor.destroy();
            }
        });
    });
</script>
@endpush