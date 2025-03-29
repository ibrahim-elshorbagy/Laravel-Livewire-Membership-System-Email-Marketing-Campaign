<div class="container p-6 mx-auto">
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
            Support
        </h2>

        <form wire:submit.prevent="sendSupportMessage" class="space-y-6" id="messageForm">
            <div class="grid gap-6 p-4 rounded-lg border md:grid-cols-2 border-neutral-200 dark:border-neutral-600">
                <!-- User Info (Read-only) -->
                <div class="grid col-span-2 gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input wire:model="name" id="name" type="text"
                            class="block mt-1 w-full bg-neutral-50 dark:bg-neutral-900" disabled />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="email" id="email" type="email"
                            class="block mt-1 w-full bg-neutral-50 dark:bg-neutral-900" disabled />
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
                <x-primary-create-button type="submit" id="sendMessageBtn">
                    Send Message
                </x-primary-create-button>
            </div>
        </form>
    </div>
    <div id="upload-indicator"
        class="hidden fixed right-4 bottom-4 z-10 p-4 bg-white rounded-lg border shadow-lg dark:bg-neutral-800 dark:border-neutral-700">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-sky-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span id="upload-progress" class="text-sm text-neutral-700 dark:text-neutral-300"></span>
        </div>
    </div>
</div>


@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $('#message').summernote({
                                height: 350,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'italic', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
                                    ['fontname', ['fontname']],
                                    ['fontsize', ['fontsize']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'picture', 'video']],
                                    ['view', ['codeview']],
                                ],
                                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
                                fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36'],
                                callbacks: {
                                    // onChange: function(contents) {
                                    //     @this.set('message', contents, true);
                                    // },
                                    onImageUpload: function(files) {
                                        for(let file of files) {
                                            uploadImage(file, this);
                                        }
                                    }
                                }
                            });
    let activeUploads = 0;

    function updateSendButtonState() {
        const sendButton = document.getElementById('sendMessageBtn');
        if (sendButton) {
            sendButton.disabled = activeUploads > 0;
            sendButton.classList.toggle('opacity-50', activeUploads > 0);
        }
    }

    function uploadImage(file, editor) {
        const uploadIndicator = document.getElementById('upload-indicator');
        const uploadProgress = document.getElementById('upload-progress');
        uploadIndicator.classList.remove('hidden');
        uploadProgress.textContent = 'Uploading image...';
        activeUploads++;
        updateSendButtonState();

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = async () => {
            const fileData = reader.result;
            try {
                const result = await @this.uploadEditorImage(fileData);
                if (result.success) {
                    uploadProgress.textContent = 'Upload completed!';
                    setTimeout(() => {
                        uploadIndicator.classList.add('hidden');
                        activeUploads--;
                        updateSendButtonState();
                    }, 2000);
                    $(editor).summernote('insertImage', result.url);
                } else {
                    uploadProgress.textContent = 'Upload failed: ' + result.error;
                    setTimeout(() => {
                        uploadIndicator.classList.add('hidden');
                        activeUploads--;
                        updateSendButtonState();
                    }, 3000);
                }
            } catch (error) {
                uploadProgress.textContent = 'Upload error: ' + error;
                setTimeout(() => {
                uploadIndicator.classList.add('hidden');
                activeUploads--;
                updateSendButtonState();
            }, 3000);
            }
        };
        reader.onerror = () => {
            uploadProgress.textContent = 'Failed to read file';
            setTimeout(() => {
                uploadIndicator.classList.add('hidden');
                activeUploads--;
                updateSendButtonState();
            }, 3000);
        };
    }
    
        const form = document.getElementById('messageForm');
        form.addEventListener('submit', function(e) {
            @this.set('message', $('#message').summernote('code'), true);
        });

    document.addEventListener('livewire:initialized', function () {
        Livewire.on('resetEditor', () => {
            $('#message').summernote('reset');
        });

        Livewire.on('disconnected', () => {
            $('#message').summernote('destroy');
        });
    });
</script>
@endpush
