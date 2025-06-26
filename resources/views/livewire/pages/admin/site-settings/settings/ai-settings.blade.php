<div class="container mx-auto">
  <div class="p-3 bg-white rounded-lg shadow-md dark:bg-neutral-800">
    <h2 class="mb-6 text-2xl font-bold text-neutral-800 dark:text-neutral-200">
      AI Settings
    </h2>
    <!-- OpenAI Configuration -->
    <div >
      <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
        OpenAI Configuration
      </h3>

      {{-- Notes --}}
      <div
        class="p-4 my-4 bg-neutral-50 dark:bg-neutral-900  text-sm text-neutral-700 dark:text-neutral-300 space-y-4 leading-relaxed">
        <p>
          To configure <span class="font-semibold text-neutral-800 dark:text-neutral-100">OpenAI integration</span>,
          you must collect the following values from your OpenAI account:
        </p>

        <ul class="list-disc list-inside space-y-2">
          <li>
            <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[0.85em] rounded">OpenAI API Key</code> –
            Generate this at your
            <a href="https://platform.openai.com/account/api-keys" target="_blank"
              class="text-blue-600 dark:text-blue-400 hover:underline ml-1">API Keys</a> page. This key authenticates
            all API requests.
          </li>
          <li>
            <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[0.85em] rounded">OpenAI Model</code> –
            Define the model ID you wish to use (e.g.,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">gpt-4</code>,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">gpt-3.5-turbo</code>). View available
            models
            <a href="https://platform.openai.com/docs/models" target="_blank"
              class="text-blue-600 dark:text-blue-400 hover:underline ml-1">here</a>.
          </li>
          <li>
            <code
              class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[0.85em] rounded">OpenAI Organization</code>
            – (Optional) Your organization ID, used for access control and billing. Find it
            <a href="https://platform.openai.com/account/organization" target="_blank"
              class="text-blue-600 dark:text-blue-400 hover:underline ml-1">here</a>.
          </li>
          <li>
            <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[0.85em] rounded">OpenAI Project</code> –
            (Optional) Your project ID, also for access control and billing. Find it
            <a href="https://platform.openai.com/settings/organization/projects" target="_blank"
              class="text-blue-600 dark:text-blue-400 hover:underline ml-1">here</a>.
          </li>
          <li>
            <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[0.85em] rounded">OpenAI Role</code> –
            (Optional) A custom role context for your assistant, such as
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">assistant</code>,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">admin</code>,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">support</code>,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">user</code>,
            <code class="mx-1 bg-neutral-100 dark:bg-neutral-800 rounded px-1">system</code>
          </li>
        </ul>

      </div>
    </div>

    <form wire:submit.prevent="updateAiSettings" class="space-y-6">

      <div class="grid gap-6 md:grid-cols-2">
        <!-- OpenAI Active Toggle -->
        <div class="md:col-span-2">
          <div class="flex items-center space-x-3">
            <input type="checkbox" wire:model="openai_active" id="openai_active"
              class="w-4 h-4 text-primary-600 bg-neutral-100 border-neutral-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-neutral-800 focus:ring-2 dark:bg-neutral-700 dark:border-neutral-600">
            <label for="openai_active" class="text-sm font-medium text-neutral-900 dark:text-neutral-300">
              Enable OpenAI Integration
            </label>
          </div>
          <x-input-error :messages="$errors->get('openai_active')" class="mt-2" />
        </div>

        <!-- API Key -->
        <div class="md:col-span-2">
          <x-input-label for="openai_api_key" :value="__('OpenAI API Key')" />
          <x-text-input wire:model="openai_api_key" id="openai_api_key" type="text" class="block mt-1 w-full"
            placeholder="sk-..." />
          <x-input-error :messages="$errors->get('openai_api_key')" class="mt-2" />
          <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
            Your OpenAI API key. Keep this secure and never share it.
          </p>
        </div>

        <!-- Model -->
        <div>
          <x-input-label for="openai_model" :value="__('OpenAI Model')" />
          <select wire:model="openai_model" id="openai_model"
            class="block mt-1 w-full border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
            <option value="gpt-4o">GPT-4o</option>
            <option value="gpt-4">GPT-4</option>
            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
            <option value="gpt-4-turbo">GPT-4 Turbo</option>
          </select>
          <x-input-error :messages="$errors->get('openai_model')" class="mt-2" />
        </div>

        <!-- Role -->
        <div>
          <x-input-label for="openai_role" :value="__('OpenAI Role')" />
          <select wire:model="openai_role" id="openai_role"
            class="block mt-1 w-full border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
            <option value="user">User</option>
            <option value="assistant">Assistant</option>
            <option value="system">System</option>
            <option value="admin">Admin</option>
            <option value="system">System</option>
          </select>
          <x-input-error :messages="$errors->get('openai_role')" class="mt-2" />
        </div>

        <!-- Organization -->
        <div>
          <x-input-label for="openai_organization" :value="__('Organization ID')" />
          <x-text-input wire:model="openai_organization" id="openai_organization" type="text" class="block mt-1 w-full"
            placeholder="org-..." />
          <x-input-error :messages="$errors->get('openai_organization')" class="mt-2" />
          <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
            Optional: Your OpenAI organization ID.
          </p>
        </div>

        <!-- Project -->
        <div>
          <x-input-label for="openai_project" :value="__('Project ID')" />
          <x-text-input wire:model="openai_project" id="openai_project" type="text" class="block mt-1 w-full"
            placeholder="proj_..." />
          <x-input-error :messages="$errors->get('openai_project')" class="mt-2" />
          <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
            Optional: Your OpenAI project ID.
          </p>
        </div>
      </div>


      <!-- Email Template Prompt -->
      <div class="p-6 rounded-lg border border-neutral-200 dark:border-neutral-600">
        <h3 class="mb-4 text-xl font-semibold text-neutral-800 dark:text-neutral-200">
          Email Template Prompt
        </h3>

        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
          <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">How to Build Your Prompt with Variables</h4>
          <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
            You can use variables in your prompt that will be automatically replaced with user answers. Use these exact variable names:
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
            <div class="flex flex-col space-y-1">
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$product_name</code> - Product Name</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$product_advantages</code> - Main Product Advantages</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$target_audience</code> - Who it's for</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$message_goal</code> - What action to take</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$contact_link</code> - URL or contact info</span>
            </div>
            <div class="flex flex-col space-y-1">
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$company_name</code> - Company/Brand Name</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$tone</code> - professional/enthusiastic/friendly</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$special_offer</code> - Discounts/promotions</span>
              <span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">$language</code> - english/arabic</span>
            </div>
          </div>
          <div class="mt-3 p-3 bg-blue-100 dark:bg-blue-800 rounded text-xs">
            <strong>Example:</strong><br>
            <code class="text-blue-900 dark:text-blue-200">
              Generate a professional HTML email template for $product_name targeting $target_audience. 
              The goal is to $message_goal. Use a $tone tone and write in $language. 
              Highlight these advantages: $product_advantages. 
              Include contact link: $contact_link from $company_name.
              @if(@$special_offer) Add this special offer: $special_offer @endif
            </code>
          </div>
        </div>

        <div>
          <x-input-label for="prompt" :value="__('Base Prompt')" />
          <textarea wire:model="prompt" id="prompt" rows="6"
            class="block mt-1 w-full border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
            placeholder="Generate a professional HTML email template for $product_name targeting $target_audience. The goal is to $message_goal. Use a $tone tone and write in $language. Highlight these advantages: $product_advantages. Include contact link: $contact_link from $company_name. Special offer: $special_offer"></textarea>
          <x-input-error :messages="$errors->get('prompt')" class="mt-2" />
          <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
            Write your custom prompt using the variables above. The system will replace each variable with the actual user input when generating emails.
          </p>
        </div>
      </div>

      <!-- API Status -->
      @if($openai_active && $openai_api_key)
      <div class="p-4 rounded-lg bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-700">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800 dark:text-green-300">
              OpenAI integration is active
            </p>
          </div>
        </div>
      </div>
      @else
      <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-700">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
              OpenAI integration is Inactive
            </p>
          </div>
        </div>
      </div>
      @endif

      <div class="flex justify-end">
        <x-primary-create-button type="submit">
          Update AI Settings
        </x-primary-create-button>
      </div>
    </form>
  </div>
</div>