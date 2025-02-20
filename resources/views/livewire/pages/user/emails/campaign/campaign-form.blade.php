<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <header class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
            {{ $campaign_id ? 'Edit Campaign' : 'New Campaign' }}
        </h2>
        <div class="mt-4 md:mt-0">
            <x-primary-info-button href="{{ route('user.campaigns.list') }}" wire:navigate>
                Back to Campaigns
            </x-primary-info-button>
        </div>
    </header>

    <form wire:submit.prevent="saveCampaign" class="space-y-6">
        <!-- Title -->
        <div>
            <x-input-label for="title" required>Campaign Title</x-input-label>
            <x-text-input wire:model="title" id="title" type="text" class="block w-full mt-1" required />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <!-- Message Selection -->
        <div x-data="{ open: false }" class="relative">
            <x-input-label for="message" required>Email Message</x-input-label>
            <div class="mt-1">
                <button type="button" @click="open = !open"
                    class="w-full px-4 py-2 text-left border rounded-md shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @if($message_id && $availableMessages->firstWhere('id', $message_id))
                    {{ $availableMessages->firstWhere('id', $message_id)->message_title }}
                    @else
                    Select Message
                    @endif
                </button>

                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-neutral-800">
                    <div class="p-2">
                        <input type="text" wire:model.live="messageSearch"
                            class="w-full px-3 py-2 border rounded-md dark:bg-neutral-700 dark:border-neutral-600"
                            placeholder="Search messages...">

                        <div class="mt-2 overflow-y-auto max-h-48">
                            @foreach($availableMessages as $message)
                            <div class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700
                                {{ $message_id == $message->id ? 'bg-sky-50 dark:bg-sky-900' : '' }}"
                                wire:click="$set('message_id', {{ $message->id }}); open = false">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $message->message_title }}</span>
                                    <span class="text-sm opacity-75">{{ $message->email_subject }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('message_id')" class="mt-2" />
        </div>

        <!-- Servers Selection -->
        <div x-data="{ open: false }" class="relative">
            <x-input-label for="servers" required>Select Servers</x-input-label>
            <div class="mt-1">
                <button type="button" @click="open = !open"
                    class="w-full px-4 py-2 text-left border rounded-md shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    {{ count($selectedServers) }} server(s) selected
                </button>

                <!-- Selected servers tags -->
                @if(count($selectedServers) > 0)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($availableServers->whereIn('id', $selectedServers) as $server)
                    <span class="inline-flex items-center px-2 py-1 text-sm bg-blue-100 rounded-full dark:bg-blue-900">
                        {{ $server->name }}
                        <button type="button"
                            wire:click="$set('selectedServers', {{ json_encode(array_values(array_diff($selectedServers, [$server->id]))) }})"
                            class="ml-1 text-blue-600 dark:text-blue-400 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                    @endforeach
                </div>
                @endif

                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-neutral-800">
                    <div class="p-2">
                        <input type="text" wire:model.live="serverSearch"
                            class="w-full px-3 py-2 border rounded-md dark:bg-neutral-700 dark:border-neutral-600"
                            placeholder="Search servers...">

                        <div class="mt-2 overflow-y-auto max-h-48">
                            @foreach($availableServers as $server)
                            <label
                                class="flex items-center px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                <input type="checkbox" wire:model.live="selectedServers" value="{{ $server->id }}"
                                    class="rounded border-neutral-300 dark:border-neutral-700">
                                <span class="ml-2">{{ $server->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('selectedServers')" class="mt-2" />
        </div>

        <!-- Email Lists Selection -->
        <div x-data="{ open: false }" class="relative">
            <x-input-label for="lists" required>Select Email Lists</x-input-label>
            <div class="mt-1">
                <button type="button" @click="open = !open"
                    class="w-full px-4 py-2 text-left border rounded-md shadow-sm dark:border-neutral-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    {{ count($selectedLists) }} list(s) selected
                </button>

                <!-- Selected lists tags -->
                @if(count($selectedLists) > 0)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($availableLists->whereIn('id', $selectedLists) as $list)
                    <span
                        class="inline-flex items-center px-2 py-1 text-sm bg-green-100 rounded-full dark:bg-green-900">
                        {{ $list->name }}
                        <button type="button"
                            wire:click="$set('selectedLists', {{ json_encode(array_values(array_diff($selectedLists, [$list->id]))) }})"
                            class="ml-1 text-green-600 dark:text-green-400 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                    @endforeach
                </div>
                @endif

                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-neutral-800">
                    <div class="p-2">
                        <input type="text" wire:model.live="listSearch"
                            class="w-full px-3 py-2 border rounded-md dark:bg-neutral-700 dark:border-neutral-600"
                            placeholder="Search lists...">

                        <div class="mt-2 overflow-y-auto max-h-48">
                            @foreach($availableLists as $list)
                            <label
                                class="flex items-center px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-700">
                                <input type="checkbox" wire:model.live="selectedLists" value="{{ $list->id }}"
                                    class="rounded border-neutral-300 dark:border-neutral-700">
                                <span class="ml-2">{{ $list->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('selectedLists')" class="mt-2" />
        </div>

        <div class="flex justify-end space-x-3">
            <x-secondary-button type="button" wire:navigate href="{{ route('user.campaigns.list') }}">
                Cancel
            </x-secondary-button>
            <x-primary-create-button type="submit">
                {{ $campaign_id ? 'Update Campaign' : 'Create Campaign' }}
            </x-primary-create-button>
        </div>
    </form>
</div>
