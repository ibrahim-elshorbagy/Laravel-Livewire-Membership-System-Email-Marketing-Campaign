<div
    x-cloak
    x-show="open"
    class="fixed top-0 left-0 z-40 w-full h-full bg-black bg-opacity-20"
    @click="open = false"
    x-transition.opacity.duration.600ms
></div>

<div
    x-cloak
    x-show="open"
    x-transition:enter="transform transition ease-in-out duration-300 sm:duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in-out duration-300 sm:duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    @click.outside="open = false"
    class="overflow-x-hidden fixed top-0 right-0 z-50 w-full h-full transition duration-300 ease-in-out transform translate-x-0 lg:w-7/12 xl:w-5/12 2xl:w-3/12"
    id="notification"
>
    <div class="overflow-y-auto absolute right-0 z-30 w-full h-screen bg-white shadow">
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <p tabindex="0" class="py-1 text-base font-semibold leading-6 text-gray-900">Notifications</p>
            <button @click="open = false" class="flex absolute top-0 right-0 z-30 justify-center items-center px-2 py-1 mt-4 mr-5 space-x-1 text-xs font-medium uppercase rounded-md border border-neutral-200 text-neutral-600 hover:bg-neutral-100">
                <x-megaphone::icons.close />
            </button>
        </div>

        <div class="p-4 pt-2">
            @if ($unread->count() > 0)
                <div class="flex justify-between pb-2 text-gray-600 border-b border-gray-300">
                    <h2 class="pt-8 text-sm leading-normal focus:outline-none">
                        Unread Notifications
                    </h2>

                    @if ($unread->count() > 1)
                        <button class="pt-8 text-sm leading-normal focus:outline-none hover:text-red-700" wire:click="markAllRead()">Mark all as read</button>
                    @endif
                </div>

                @foreach ($unread as $announcement)
                    <div class="w-full p-3 mt-4 bg-white rounded-xl flex flex-shrink-0 {{ $announcement->read_at === null ? "drop-shadow shadow border" : ""  }}">
                        <x-megaphone::display :notification="$announcement"></x-megaphone::display>

                        @if($announcement->read_at === null)
                            <button role="button" aria-label="Mark as Read" class="absolute top-0 right-0 px-1 py-1 mt-2 mr-2 space-x-1 rounded-md border cursor-pointer outline-none border-neutral-200 text-neutral-600 hover:bg-neutral-100"
                                    x-on:click="$wire.markAsRead('{{ $announcement->id }}')"
                                    title="Mark as Read"
                            >
                                <x-megaphone::icons.read class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                @endforeach
            @endif


            @if ($announcements->count() > 0)
                <div class="flex justify-between pb-2 text-gray-600 border-b border-gray-300">
                    <h2 tabindex="0" class="pt-8 text-sm leading-normal focus:outline-none">
                        Previous Notifications
                    </h2>

                    @if($allowDelete)
                        <button class="pt-8 text-sm leading-normal focus:outline-none hover:text-red-500"
                            wire:click="deleteAllReadNotification">
                            Clear all
                        </button>
                    @endif
                </div>
            @endif

            @foreach ($announcements as $announcement)
                <div class="flex relative flex-shrink-0 p-3 mt-4 w-full bg-gray-50 rounded">
                    <x-megaphone::display :notification="$announcement"></x-megaphone::display>

                    @if($allowDelete)
                        <button role="button" aria-label="Delete" class="absolute top-0 right-0 px-1 py-1 mt-2 mr-2 space-x-1 rounded-md border cursor-pointer outline-none border-neutral-200 text-neutral-600 hover:bg-neutral-200"
                            x-on:click="$wire.deleteNotification('{{ $announcement->id }}')"
                            title="Delete notification"
                        >
                            <x-megaphone::icons.delete class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            @endforeach

            @if ($unread->count() === 0 && $announcements->count() === 0)
                <div class="flex justify-between items-center">
                    <hr class="w-full">
                    <p tabindex="0" class="flex flex-shrink-0 px-3 py-16 text-sm leading-normal text-gray-500 focus:outline-none">
                        No New Noitifcation
                    </p>
                    <hr class="w-full">
                </div>
            @endif
        </div>
    </div>
</div>

