<div class="flex flex-col space-y-4 h-full" wire:poll.2s="pollForNewMessages">
    {{--  --}}
    <div class="overflow-y-auto flex-1 py-2 space-y-3 sm:space-y-4">
        @foreach($conversations as $conversation)
        @php
        $isAdmin = collect($conversation['user']['roles'])->contains('name', 'admin');
        @endphp
        <div wire:key="conversation-{{ $conversation['id'] }}"
            class="flex items-start gap-2 sm:gap-2.5 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
            <div
                class="flex flex-col w-full max-w-[95%] sm:max-w-[95%] leading-1.5 p-3 sm:p-4 border-neutral-200 bg-neutral-100 rounded-e-xl rounded-es-xl dark:bg-neutral-700">
                <div
                    class="flex flex-col mb-2 space-y-1 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-2 rtl:space-x-reverse">
                    <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                        {{ $isAdmin ? 'Support Team' : $conversation['user']['first_name'] . ' ' .
                        $conversation['user']['last_name'] }}
                    </span>
                    <span class="text-xs font-normal sm:text-sm text-neutral-500 dark:text-neutral-400">
                        {{ \Carbon\Carbon::parse($conversation['created_at'])->timezone($time_zone)->format('d/m/Y h:i A') }} -
                        {{ \Carbon\Carbon::parse($conversation['created_at'])->timezone($time_zone)->diffForHumans() }}
                    </span>
                </div>
                <div
                    class="text-sm font-normal break-words text-neutral-700 dark:text-neutral-200 no-tailwindcss-support-display">
                    {!! $conversation['message'] !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @php
        $isCurrentUserAdmin = auth()->user()->roles->contains('name', 'admin');
        $isCurrentUserAllowed = $isCurrentUserAdmin || (!isset($ticket->closed_at) && auth()->user()->roles->contains('name', 'user'));
    @endphp
    @if($isCurrentUserAllowed)
    <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-700">
        <form wire:submit.prevent="sendMessage" id="messageForm">
            <div x-cloak class="mb-3 no-tailwindcss-support-display">
                <div wire:ignore>
                    <textarea id="message" class="block mt-1 w-full"></textarea>
                </div>
                <input type="hidden" id="hiddenMessage" wire:model="message">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-create-button type="submit" wire:target="sendMessage" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50">
                    <span wire:target="sendMessage" wire:loading.remove>Send Message</span>
                    <span wire:target="sendMessage" wire:loading>Sending...</span>
                </x-primary-create-button>
            </div>
        </form>
    </div>
    @endif

    <div id="upload-indicator"
        class="hidden fixed right-4 bottom-4 p-4 bg-white rounded-lg border shadow-lg dark:bg-neutral-800 dark:border-neutral-700">
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
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('livewire:initialized', function () {
        let editor;
        const form = document.querySelector('#messageForm');

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

                // if (@this.message) {
                //     editor.setData(@this.message);
                // }

                // Update Livewire's message property just before submission
                form.addEventListener('submit', function(e) {
                    // Update Livewire's message property before submission
                    @this.set('message', editor.getData(), true);
                });
                // editor.model.document.on('change:data', () => {
                //     @this.set('message', editor.getData());
                // });

                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    return {
                        upload: async () => {

                            const uploadIndicator = document.getElementById('upload-indicator');
                            const uploadProgress = document.getElementById('upload-progress');
                            uploadIndicator.classList.remove('hidden');
                            uploadProgress.textContent = 'Uploading image...';

                            const file = await loader.file;

                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = async () => {
                                    const fileData = reader.result;

                                    try {
                                        const result = await @this.uploadCKEditorImage(fileData);

                                        if (result.success) {
                                            uploadProgress.textContent = 'Upload completed!';
                                            setTimeout(() => uploadIndicator.classList.add('hidden'), 2000);
                                            resolve({ default: result.url });
                                        } else {
                                            uploadProgress.textContent = 'Upload failed: ' + result.error;
                                            setTimeout(() => uploadIndicator.classList.add('hidden'), 3000);
                                            reject(result.error);
                                        }
                                    } catch (error) {
                                        uploadProgress.textContent = 'Upload error: ' + error;
                                        setTimeout(() => uploadIndicator.classList.add('hidden'), 3000);
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

        Livewire.on('disconnected', () => {
            if (editor) {
                editor.destroy();
            }
        });

        Livewire.on('resetEditor', () => {
            if (editor) {
                editor.setData('');
            }
        });
    });
</script>
@endpush