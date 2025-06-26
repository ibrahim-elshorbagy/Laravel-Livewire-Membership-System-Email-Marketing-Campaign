<div
  class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
  <header class="flex flex-col justify-between items-center mb-6 md:flex-row">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate"> {{
      $message_id ? 'Edit Message' : 'New Message' }} </h2>
    <div class="mt-4 md:mt-0">
      <x-primary-info-link href="{{ route('user.email-messages') }}" wire:navigate> Back To Messages
      </x-primary-info-link>
    </div>
  </header>



  <form wire:submit.prevent="saveMessage" class="space-y-4" id="messageForm"
    x-data="{ localMessageHtml: @js($message_html) }">
    <div class="grid grid-cols-1 gap-6 h-full lg:grid-cols-2">
      <!-- Form Section -->
      <div class="overflow-y-auto space-y-4">
        <div>
          <x-input-label for="message_title">Message Title</x-input-label>
          <x-text-input wire:model="message_title" id="message_title" type="text" class="block mt-1 w-full" />
          <x-input-error :messages="$errors->get('message_title')" class="mt-2" />
        </div>
        <div>
          <x-input-label for="email_subject">Email Subject</x-input-label>
          <x-text-input wire:model="email_subject" id="email_subject" type="text" class="block mt-1 w-full" />
          <x-input-error :messages="$errors->get('email_subject')" class="mt-2" />
        </div>


        <div class="mb-4" x-data="{ activeEditor: @entangle('activeEditor').live }">
          <div class="flex justify-between items-center mb-4">
            <x-input-label>HTML Template</x-input-label>
            <div class="flex items-center space-x-4">
              <!-- AI Generation Button -->
              @if(App\Models\Admin\Site\SiteSetting::getValue('openai_active', false))
              <button type="button" x-on:click="$dispatch('open-modal', 'ai-generation-modal')"
                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-md hover:from-purple-600 hover:to-blue-600 transition-all flex items-center space-x-2">
                <i class="fas fa-magic"></i>
                <span>Generate with AI</span>
              </button>
              @endif

              <button type="button"
                @click="$wire.set('activeEditor', 'advanced'); $dispatch('editor-changed', { editor: 'advanced' })"
                :class="{'bg-blue-500 text-white': activeEditor === 'advanced', 'bg-gray-200 dark:bg-neutral-700': activeEditor != 'advanced'}"
                class="px-4 py-2 rounded-md transition-colors">
                Advanced Editor
              </button>
              <button type="button"
                @click="$wire.set('activeEditor', 'code'); $dispatch('editor-changed', { editor: 'code' })"
                :class="{'bg-blue-500 text-white': activeEditor === 'code', 'bg-gray-200 dark:bg-neutral-700': activeEditor != 'code'}"
                class="px-4 py-2 rounded-md transition-colors">
                Code Editor
              </button>
            </div>
          </div>

          <div wire:ignore x-show="activeEditor === 'advanced'">
            <div class="mt-1 space-y-2">
              <div id="text-editor" class="min-h-[350px]"></div>
            </div>
          </div>

          <div wire:ignore x-show="activeEditor === 'code'">
            <div class="mt-1 space-y-2">
              <div id="editor-container" class="overflow-hidden rounded-md border dark:border-neutral-700"
                style="height: 400px; min-height: 200px; max-height: 800px; resize: vertical;"> </div>
              <textarea id="editor" wire:model.live="message_html" class="hidden"></textarea>
            </div>
          </div>


          <div x-data="htmlSizeChecker()"
            x-init="init($wire.html_size_limit, $wire.base64_image_size_limit, $wire.message_html)"
            @editor-content-updated.window="checkSizes($event.detail.content)"
            class="p-4 mt-4 space-y-3 text-sm text-gray-700 bg-gray-100 rounded-xl dark:bg-neutral-800 dark:text-gray-300">
            <!-- Current HTML Size -->
            <div x-show="currentSize > 0"
              class="flex justify-between items-center p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white/60 dark:bg-white/10">
              <span class="font-semibold">Current size:</span>
              <span
                class="px-2 py-1 ml-2 text-xs text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200"
                x-text="`${formatKB(currentSize)} (${formatMB(currentSize)})`">
              </span>
            </div>

            <!-- HTML Size Limit Warning -->
            <div x-show="currentSize > maxHtmlSize" class="flex items-center text-red-600 dark:text-red-400">
              <i class="mr-2 fas fa-exclamation-triangle"></i>
              <span>
                Warning: HTML content exceeds maximum size of
                <span x-text="maxHtmlSizeKB" class="font-semibold"></span> KB!
              </span>
            </div>

            <!-- Large Image Warning -->
            <div x-show="hasLargeImages" class="flex items-center text-red-600 dark:text-red-400">
              <i class="mr-2 fas fa-exclamation-triangle"></i>
              <span>
                Warning: Contains images larger than
                <span x-text="maxImageSizeKB" class="font-semibold"></span> KB!
              </span>
            </div>
          </div>


        </div>


        <div class="no-tailwindcss-support-display">
          <x-input-label for="message_plain_text">Message Plain Text</x-input-label>
          <x-primary-textarea wire:model="message_plain_text" id="message_plain_text" rows="16"
            class="block mt-1 w-full"> </x-primary-textarea>
          {{--
          <x-input-error :messages="$errors->get('message_plain_text')" class="mt-2" /> --}}
        </div>

        <div class="p-4 mb-4 bg-white rounded-lg border dark:bg-neutral-800 dark:border-neutral-700">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <i class="w-5 h-5 text-blue-500 fas fa-info-circle"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Message Personalization
              </h3>
              <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                <p class="mb-3">
                  A powerful feature of GeMailApp is the ability to personalize outgoing messages with
                  the name and/or email address of
                  the recipient.
                  To take advantage of this feature you need only specify the personalization fields
                  in your
                  text of your message. As the message is sent the fields are replaced with the
                  appropriate value for each
                  message recipient.
                </p>
                <div class="mt-3 space-y-2">
                  <div class="flex items-center">
                    <code class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%FullName%</code>
                    <span class="ml-2">Replaced with the Full Name of the member</span>
                  </div>

                  <p class="mb-3">
                    Full Name field is filled automatically when retrieving e-mail addresses if the
                    information is available.
                  </p>

                  <div class="flex items-center">
                    <code class="px-2 py-1 font-mono text-sm bg-gray-100 rounded dark:bg-neutral-700">%Email%</code>
                    <span class="ml-2">Replaced with the e-mail address of the user</span>
                  </div>
                </div>




              </div>
            </div>
          </div>
        </div>


        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <x-input-label for="sender_name">Sender Name</x-input-label>
            <x-text-input wire:model="sender_name" id="sender_name" type="text" class="block mt-1 w-full" />
            <x-input-error :messages="$errors->get('sender_name')" class="mt-2" />
          </div>
          <div>
            <x-input-label for="reply_to_email">Reply To Email</x-input-label>
            <x-text-input wire:model="reply_to_email" id="reply_to_email" type="email" class="block mt-1 w-full" />
            <x-input-error :messages="$errors->get('reply_to_email')" class="mt-2" />
          </div>
        </div>
        <div class="flex sticky bottom-0 justify-end py-4 space-x-3 bg-neutral-50 dark:bg-neutral-900">
          <x-secondary-button type="button" wire:navigate href="{{ route('user.email-messages') }}"> Cancel
          </x-secondary-button>
          <x-primary-create-button type="submit"> {{ $message_id ? 'Update Message' : 'Create Message' }}
          </x-primary-create-button>
        </div>
      </div>




      <!-- Preview Section -->
      <div class="h-full rounded-lg border dark:border-neutral-700">
        <div class="flex justify-between items-center p-4 border-b dark:border-neutral-700">
          <h3 class="text-lg font-semibold">Preview</h3>
        </div>
        <div class="h-[calc(100vh-200px)] md:h-[calc(100%-60px)] overflow-hidden">
          <iframe id="preview-frame" class="w-full h-full bg-white border-0 dark:bg-neutral-800"
            sandbox="allow-same-origin allow-scripts allow-popups allow-forms" referrerpolicy="no-referrer">
          </iframe>
        </div>
      </div>

    </div>
  </form>
  @if ($errors->any())
  <div class="p-4 mt-4 bg-red-50 rounded-md border border-red-200 dark:bg-red-900/20 dark:border-red-800">
    <div class="flex">
      <div class="flex-shrink-0">
        <i class="w-5 h-5 text-red-400 fas fa-times-circle"></i>
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
          There were errors with your submission
        </h3>
        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
          <ul class="pl-5 space-y-1 list-disc">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- AI Generation Modal -->
  <x-modal name="ai-generation-modal" maxWidth="4xl">
    <div class="p-6">
      <h2 class="text-xl font-bold text-neutral-800 dark:text-neutral-200 mb-6">
        <i class="fas fa-magic text-purple-500 mr-2"></i>
        Generate Email Template with AI
      </h2>

      <form wire:submit.prevent="generateAIMessage" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Product Name -->
          <div>
            <x-input-label for="ai_product_name" value="Product Name *" />
            <x-text-input wire:model="ai_product_name" id="ai_product_name" type="text" class="block mt-1 w-full"
              placeholder="e.g., Marketing Tool, Software, Food Product" />
            <x-input-error :messages="$errors->get('ai_product_name')" class="mt-2" />
          </div>

          <!-- Company Name -->
          <div>
            <x-input-label for="ai_company_name" value="Company/Brand Name *" />
            <x-text-input wire:model="ai_company_name" id="ai_company_name" type="text" class="block mt-1 w-full"
              placeholder="Your company or brand name" />
            <x-input-error :messages="$errors->get('ai_company_name')" class="mt-2" />
          </div>

          <!-- Target Audience -->
          <div>
            <x-input-label for="ai_target_audience" value="Target Audience *" />
            <x-text-input wire:model="ai_target_audience" id="ai_target_audience" type="text" class="block mt-1 w-full"
              placeholder="e.g., Small businesses, Young professionals, Age 25-40" />
            <x-input-error :messages="$errors->get('ai_target_audience')" class="mt-2" />
          </div>

          <!-- Message Goal -->
          <div>
            <x-input-label for="ai_message_goal" value="Message Goal *" />
            <x-text-input wire:model="ai_message_goal" id="ai_message_goal" type="text" class="block mt-1 w-full"
              placeholder="e.g., Purchase, Free trial, Website visit, Book appointment" />
            <x-input-error :messages="$errors->get('ai_message_goal')" class="mt-2" />
          </div>

          <!-- Contact Link -->
          <div>
            <x-input-label for="ai_contact_link" value="Contact Link/Website" />
            <x-text-input wire:model="ai_contact_link" id="ai_contact_link" type="text" class="block mt-1 w-full"
              placeholder="https://yourwebsite.com or email@example.com" />
            <x-input-error :messages="$errors->get('ai_contact_link')" class="mt-2" />
          </div>

          <!-- Tone -->
          <div>
            <x-input-label for="ai_tone" value="Email Tone *" />
            <x-text-input wire:model="ai_tone" id="ai_tone" type="text" class="block mt-1 w-full"
              placeholder="Email Tone" />
            <x-input-error :messages="$errors->get('ai_tone')" class="mt-2" />

          </div>

          <!-- Language -->
          <div>
            <x-input-label for="ai_language" value="Language *" />
            <select wire:model="ai_language" id="ai_language"
              class="block mt-1 w-full border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
              <option value="english">English</option>
              <option value="arabic">Arabic</option>
            </select>
            <x-input-error :messages="$errors->get('ai_language')" class="mt-2" />
          </div>

          <!-- Special Offer -->
          <div>
            <x-input-label for="ai_special_offer" value="Special Offer/Discount (Optional)" />
            <x-text-input wire:model="ai_special_offer" id="ai_special_offer" type="text" class="block mt-1 w-full"
              placeholder="e.g., 20% off, Free trial, Limited time offer" />
            <x-input-error :messages="$errors->get('ai_special_offer')" class="mt-2" />
          </div>
        </div>

        <!-- Product Advantages -->
        <div>
          <x-input-label for="ai_product_advantages" value="Main Product Advantages *" />
          <textarea wire:model="ai_product_advantages" id="ai_product_advantages" rows="3"
            class="block mt-1 w-full border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
            placeholder="What makes your product special? List the key benefits and advantages..."></textarea>
          <x-input-error :messages="$errors->get('ai_product_advantages')" class="mt-2" />
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
          <x-secondary-button x-on:click="$dispatch('close-modal', 'ai-generation-modal')" type="button">
            Cancel
          </x-secondary-button>
          <x-primary-create-button type="submit" wire:loading.attr="disabled" wire:target="generateAIMessage">
            <span wire:loading.remove wire:target="generateAIMessage">
              <i class="fas fa-magic mr-2"></i>Generate Email
            </span>
            <span wire:loading wire:target="generateAIMessage">
              <i class="fas fa-spinner fa-spin mr-2"></i>Generating...
            </span>
          </x-primary-create-button>
        </div>
      </form>
    </div>
  </x-modal>
</div>





@push('scripts')
@vite('resources/js/AdvanceTinyMCE.js')
<script>
  document.addEventListener('livewire:initialized', function() {
        const form = document.getElementById('messageForm');
        if (form && !window.advanceCodeEditor.initialized) {
            window.advanceCodeEditor.init();

            // Handle form submission
            form.addEventListener('submit', function() {
                @this.set('message_html', window.advanceCodeEditor.getContent());
            });

            // Handle Livewire updates
            Livewire.on('message-updated', () => {
                if (window.advanceCodeEditor) {
                    window.advanceCodeEditor.destroyEditor();
                    setTimeout(() => {
                        window.advanceCodeEditor.init();
                    }, 100);
                }
            });
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('htmlSizeChecker', () => ({
            currentSize: 0,
            maxHtmlSize: 0,
            maxHtmlSizeKB: 0,
            maxImageSize: 0,
            maxImageSizeKB: 0,
            hasLargeImages: false,

            init(htmlSizeLimitKB, imageSizeLimitKB, initialContent = '') {
                this.maxHtmlSizeKB = htmlSizeLimitKB;
                this.maxHtmlSize = htmlSizeLimitKB * 1024;
                this.maxImageSizeKB = imageSizeLimitKB;
                this.maxImageSize = imageSizeLimitKB * 1024;

                // Check initial content immediately
                this.checkSizes(initialContent);

                // Listen for editor changes
                Livewire.hook('message.processed', (component) => {
                    if (component.fingerprint.name === 'pages.user.emails.message-form') {
                        this.checkSizes(component.serverMemo.data.message_html);
                    }
                });

                // Also check the editor content after it initializes
                setTimeout(() => {
                    const editorContent = this.getEditorContent();
                    if (editorContent && editorContent != initialContent) {
                        this.checkSizes(editorContent);
                    }
                }, 500);
            },

            getEditorContent() {
                // Try to get content from active editor
                if (window.advanceCodeEditor?.editor) {
                    return window.advanceCodeEditor.editor.getContent();
                }

                const codeEditor = document.querySelector('.cm-content');
                if (codeEditor) {
                    return codeEditor.textContent;
                }

                return '';
            },

            checkSizes(htmlContent) {
                if (!htmlContent) {
                    this.currentSize = 0;
                    this.hasLargeImages = false;
                    return;
                }

                this.currentSize = new Blob([htmlContent]).size;
                this.hasLargeImages = this.checkImageSizes(htmlContent);
            },

            checkImageSizes(htmlContent) {
                const imgRegex = /<img[^>]+src="data:image\/[^;]+;base64,([^"]+)"/gi;
                const matches = [...htmlContent.matchAll(imgRegex)];

                return matches.some(match => {
                    const base64Data = match[1];
                    const size = (base64Data.length * 0.75);
                    return size > this.maxImageSize;
                });
            },

            formatKB(bytes) {
                return `${(bytes / 1024).toFixed(2)} KB`;
            },

            formatMB(bytes) {
                return `${(bytes / (1024 * 1024)).toFixed(4)} MB`;
            }
        }));
    });


</script>
@endpush


@push('scripts')
@vite('resources/js/codeEditor.js')
@endpush