<div class="container p-6 mx-auto">
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
            Support
        </h2>

        <form wire:submit.prevent="sendSupportMessage" class="space-y-6">
            <div class="grid gap-6 p-4 rounded-lg border md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <!-- User Info (Read-only) -->
                <div class="grid col-span-2 gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" readonly />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" readonly />
                </div>
                </div>


                <!-- Subject -->
                <div>
                    <x-input-label for="subject" :value="__('Subject')" />
                    <x-text-input wire:model="subject" id="subject" type="text" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <!-- Message -->
                <div x-cloak class="lg:col-span-2 no-tailwindcss-support-display">
                    <x-input-label for="message" required>Message</x-input-label>
                    <div wire:ignore>
                        <textarea id="message" class="block mt-1 w-full"></textarea>
                    </div>
                    <input type="hidden" wire:model="message">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-create-button type="submit">
                    Send Message
                </x-primary-create-button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('livewire:initialized', function () {
        let editor;

        ClassicEditor
            .create(document.querySelector('#message'), {
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
                if (@this.message) {
                    editor.setData(@this.message);
                }

                // Update Livewire model when content changes
                editor.model.document.on('change:data', () => {
                    @this.set('message', editor.getData());
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
