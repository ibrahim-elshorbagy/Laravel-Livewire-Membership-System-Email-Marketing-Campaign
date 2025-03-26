<div
        class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div>

                <h2 class="text-xl md:text-2xl font-semibold text-neutral-800 dark:text-neutral-200">Subject: {{
                    $ticket->subject }}</h2>
                <p class="my-1 md:my-2 text-xs md:text-sm text-neutral-600 dark:text-neutral-400">Submitted by :</p>
                <div class="flex gap-2 items-center mb-4 md:mb-6 w-max">
                    <img class="object-cover rounded-full size-8 md:size-10"
                        src="{{ $ticket->user->image_url ?? asset('default-avatar.png') }}"
                        alt="{{ $ticket->user->first_name }} {{ $ticket->user->last_name }}" />
                    <div class="flex flex-col">
                        <span class="text-sm md:text-base text-neutral-900 dark:text-neutral-100 flex-col flex md:flex-row">
                            {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
                            - <a onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $ticket->user->id }})"
                                class="text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 cursor-pointer">
                                ({{ $ticket->user->username }})
                            </a>
                        </span>
                        <span class="text-xs md:text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                            {{ $ticket->user->email }}
                        </span>
                    </div>
                </div>

                @if($ticket->user_subscription)
                <div class="mb-4 p-4 bg-white rounded-lg shadow dark:bg-neutral-800">
                    <h3 class="mb-3 text-lg font-medium text-neutral-900 dark:text-neutral-100">Subscription Information
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Plan</span>
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{
                                $ticket->user_subscription->plan->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Status</span>
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full {{ !$ticket->user_subscription->canceled_at ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $ticket->user_subscription->canceled_at ? 'Cancelled' : 'Active' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Start Date</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $ticket->user_subscription->started_at }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Expiry Date</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $ticket->user_subscription->expired_at }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Remaining Time</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $ticket->user_subscription->remaining_time }}</span>
                        </div>

                        @if($ticket->user_subscription->plan->features->isNotEmpty())
                        <div class="mt-4">
                            <h4 class="mb-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">Features Usage
                            </h4>
                            <div class="space-y-3">
                                @foreach($ticket->user_subscription->plan->features as $feature)
                                @php
                                $balance = $ticket->user->balance($feature->name);
                                $charges = $feature->pivot->charges;
                                $percentage = $charges > 0 ? ($balance / $charges) * 100 : 0;
                                $colorClass = $percentage > 75 ? 'bg-green-500' : ($percentage > 25 ? 'bg-yellow-500' :
                                'bg-red-500');
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                            @if($feature->name == 'Subscribers Limit')
                                            Number of contacts
                                            @elseif($feature->name == 'Email Sending')
                                            Emails per month
                                            @else
                                            {{ $feature->name }}
                                            @endif
                                        </span>
                                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                            {{ (int)$balance }} / {{ (int)$charges }}
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-neutral-700">
                                        <div class="h-2 rounded-full transition-all {{ $colorClass }}"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <span class="mx-1 text-sm md:text-base">•</span>
                <span class="text-sm md:text-base">{{ $ticket->created_at->format('d/m/Y H:i:s') }} - {{
                    $ticket->created_at->diffForHumans() }}</span>

                <div class="flex justify-between items-center flex-col md:flex-row gap-3">
                    <div class="flex flex-wrap gap-1 md:gap-2 items-center text-xs md:text-sm">
                        <span>Status :</span>
                        <button wire:click="updateStatus('open')"
                            class="px-2 md:px-3 py-0.5 md:py-1 rounded-full {{ $ticket->status === 'open' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-neutral-100 text-neutral-600 hover:bg-yellow-50 hover:text-yellow-700 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-yellow-900 dark:hover:text-yellow-300' }}">
                            Open
                        </button>
                        <button wire:click="updateStatus('in_progress')"
                            class="px-2 md:px-3 py-0.5 md:py-1 rounded-full {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-neutral-100 text-neutral-600 hover:bg-blue-50 hover:text-blue-700 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-blue-900 dark:hover:text-blue-300' }}">
                            In Progress
                        </button>
                        <button wire:click="updateStatus('closed')"
                            class="px-2 md:px-3 py-0.5 md:py-1 rounded-full {{ $ticket->status === 'closed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-neutral-100 text-neutral-600 hover:bg-green-50 hover:text-green-700 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-green-900 dark:hover:text-green-300' }}">
                            Closed
                        </button>
                    </div>
                    <div class="flex items-center gap-4 mb-2 md:self-end self-center">
                        <x-primary-info-button href="{{ route('admin.support.tickets') }}" wire:navigate>
                            Back To Tickets
                        </x-primary-info-button>
                    </div>
                </div>
        </div>

        <div class="pt-4 border-t border-neutral-200 dark:border-neutral-600">
            <dl class="text-sm divide-y divide-neutral-200 dark:divide-neutral-600">
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Ticket Submitted</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->created_at->format('d/m/Y
                        h:i:s A') }}</dd>
                </div>
                @isset($ticket->closed_at)
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Closed</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->closed_at?->format('d/m/Y
                        h:i:s A') }}</dd>
                </div>
                @endisset
                @isset($ticket->responded_at)
                <div class="grid grid-cols-2 gap-4 py-3">
                    <dt class="text-neutral-600 dark:text-neutral-400">Responded</dt>
                    <dd class="text-neutral-800 dark:text-neutral-200">{{ $ticket->responded_at?->format('d/m/Y
                        h:i:s A') }}</dd>
                </div>
                @endisset
            </dl>
        </div>

        <div
            class="mb-8 text-neutral-600 dark:text-neutral-400 no-tailwindcss-support-display border-t border-neutral-200 dark:border-neutral-600">
            {!! $ticket->message !!}
        </div>

        @if($ticket->admin_response)
        <div class="pt-6 mb-8 border-t border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-200">Admin Response</h3>
            <div
                class="mb-8 text-neutral-600 dark:text-neutral-400 no-tailwindcss-support-display border-t border-neutral-200 dark:border-neutral-600">
                {!! $ticket->admin_response !!}
            </div>
        </div>
        @endif




        @empty($ticket->admin_response)
        <div class="pt-6 border-t border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-200">Send Response</h3>
            <form wire:submit.prevent="sendResponse">
                <div x-cloak class="lg:col-span-2 no-tailwindcss-support-display mb-5">
                    <div wire:ignore>
                        <textarea id="response" class="block mt-1 w-full"></textarea>
                    </div>
                    <input type="hidden" wire:model="response">
                    <x-input-error :messages="$errors->get('response')" class="mt-2" />
                </div>

                <x-primary-create-button type="submit">
                    Send Response
                </x-primary-create-button>
            </form>
        </div>
        @endempty
</div>



@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('livewire:initialized', function () {
        let editor;

        ClassicEditor
            .create(document.querySelector('#response'), {
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
                if (@this.response) {
                    editor.setData(@this.response);
                }

                // Update Livewire model when content changes
                editor.model.document.on('change:data', () => {
                    @this.set('response', editor.getData());
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