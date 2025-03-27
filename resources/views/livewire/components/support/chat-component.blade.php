<div class="flex flex-col space-y-4 h-full" wire:poll.5000ms="pollForNewMessages">
    <div class="overflow-y-auto flex-1 py-2 space-y-3 sm:space-y-4">
        @foreach($conversations as $conversation)
        <div
            class="flex items-start gap-2 sm:gap-2.5 {{ $conversation->user->hasRole('admin') ? 'flex-row-reverse' : '' }}">
            <div
                class="flex flex-col w-full max-w-[95%] sm:max-w-[95%] leading-1.5 p-3 sm:p-4 border-neutral-200 bg-neutral-100 rounded-e-xl rounded-es-xl dark:bg-neutral-700">
                <div
                    class="flex flex-col mb-2 space-y-1 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-2 rtl:space-x-reverse">
                    <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                        {{ $conversation->user->hasRole('admin') ? 'Support Team' : $conversation->user->first_name . '
                        ' . $conversation->user->last_name }}
                    </span>
                    <span class="text-xs font-normal sm:text-sm text-neutral-500 dark:text-neutral-400">
                        {{ $conversation->created_at->timezone($time_zone)->format('d/m/Y h:i A') }} -
                        {{ $conversation->created_at->timezone($time_zone)->diffForHumans() }}
                    </span>
                </div>
                <div
                    class="text-sm font-normal break-words text-neutral-700 dark:text-neutral-200 no-tailwindcss-support-display">
                    {!! $conversation->message !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(
    (auth()->user()->hasRole('admin')) ||
    (!isset($ticket->closed_at) && auth()->user()->hasRole('user'))
    )
    <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-700">
        <form wire:submit.prevent="sendMessage">
            <div x-cloak class="mb-3 no-tailwindcss-support-display">
                <div wire:ignore>
                    <textarea id="message" class="block mt-1 w-full"></textarea>
                </div>
                <input type="hidden" wire:model="message">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-create-button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove>Send Message</span>
                    <span wire:loading>Sending...</span>
                </x-primary-create-button>
            </div>
        </form>
    </div>
    @endif
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

                if (@this.message) {
                    editor.setData(@this.message);
                }

                editor.model.document.on('change:data', () => {
                    @this.set('message', editor.getData());
                });

                editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                    return {
                        upload: async () => {
                            const file = await loader.file;

                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = async () => {
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