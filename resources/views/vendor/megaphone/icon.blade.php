<button type="button"
        aria-label="show notifications"
        class="mx-4 font-sans text-gray-900"
        @click="open = true"
>
    <span class="sr-only">Show Notifications</span>
    <x-megaphone::icons.bell />
    @if ($unread->count() > 0)
        @if($showCount)
            <sub class="absolute top-1 left-6" aria-label="unread count">
                <span class="flex relative -mt-1 w-5 h-5">
                    <span class="inline-flex absolute w-full h-full bg-red-400 rounded-full opacity-75 motion-safe:animate-ping"></span>
                    <span class="inline-flex relative w-5 h-5 text-xs text-center text-white bg-red-500 rounded-full aspect-square">
                        <span class="w-full leading-5">
                            {{ $unread->count() > 9 ? '9+' : $unread->count() }}
                        </span>
                    </span>
                </span>
            </sub>
        @else
            <sub class="absolute top-2 left-2" aria-label="has unread notifications">
                <span class="flex relative -mt-1 w-3 h-3">
                  <span class="inline-flex absolute w-full h-full bg-red-400 rounded-full opacity-75 motion-safe:animate-ping"></span>
                  <span class="inline-flex relative w-3 h-3 bg-red-500 rounded-full"></span>
                </span>
            </sub>
        @endif
    @endif
</button>
