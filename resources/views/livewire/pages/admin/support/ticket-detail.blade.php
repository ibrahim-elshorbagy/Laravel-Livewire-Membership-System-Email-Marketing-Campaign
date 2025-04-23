<div
        class="flex flex-col p-3 rounded-md border md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">

        <div>

                <h2 class="text-xl font-semibold md:text-2xl text-neutral-800 dark:text-neutral-200">Subject: {{
                    $ticket->subject }}</h2>
                <p class="my-1 text-xs md:my-2 md:text-sm text-neutral-600 dark:text-neutral-400">Submitted by :</p>
                <div class="flex gap-2 items-center mb-4 w-max md:mb-6">
                    <img class="object-cover rounded-full size-8 md:size-10"
                        src="{{ $ticket->user->image_url ?? asset('default-avatar.png') }}"
                        alt="{{ $ticket->user->first_name }} {{ $ticket->user->last_name }}" />
                    <div class="flex flex-col">
                        <span class="flex flex-col text-sm md:text-base text-neutral-900 dark:text-neutral-100 md:flex-row">
                            {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
                            - <a onclick="confirm('Are you sure you want to impersonate this user?') || event.stopImmediatePropagation()"
                                wire:click="impersonateUser({{ $ticket->user->id }})"
                                class="text-sky-600 cursor-pointer hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300">
                                ({{ $ticket->user->username }})
                            </a>
                        </span>
                        <span class="text-xs md:text-sm text-neutral-600 opacity-85 dark:text-neutral-400">
                            {{ $ticket->user->email }}
                        </span>
                    </div>
                </div>

                @if($user_subscription)
                <div  class="p-4 mb-4 bg-white rounded-lg shadow dark:bg-neutral-800">
                    <h3 class="mb-3 text-lg font-medium text-neutral-900 dark:text-neutral-100">Subscription Information
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Plan</span>
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{
                                $user_subscription->plan->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Status</span>
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full {{ !$user_subscription->canceled_at ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $user_subscription->canceled_at ? 'Cancelled' : 'Active' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Start Date</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $user_subscription->started_at }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Expiry Date</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $user_subscription->expired_at }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Remaining Time</span>
                            <span class="text-sm text-neutral-900 dark:text-neutral-100">{{
                                $user_subscription->remaining_time }}</span>
                        </div>

                        @if($user_subscription->plan->features->isNotEmpty())
                        <div class="mt-4">
                            <h4 class="mb-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">Features Usage</h4>
                            <div class="space-y-3">
                                @foreach($user_subscription->plan->features as $feature)
                                @php
                                $charges = $feature->pivot->charges; // Total allowed
                                $balance = $ticket->user->balance($feature->name); // Currently remaining
                                $used = $charges - $balance; // Calculate how much has been used
                                $percentage = $charges > 0 ? ($used / $charges) * 100 : 0; // Percentage used
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">
                                            {{ $feature->name }}
                                        </span>
                                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                            {{ (int)$used }} / {{ (int)$charges }}
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-neutral-700">
                                        <div class="h-2 bg-green-500 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif


                <div class="flex flex-col gap-3 justify-between items-center my-4 md:flex-row">
                    <div class="flex flex-wrap gap-1 items-center text-xs md:gap-2 md:text-sm">
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
                    <div class="flex gap-4 items-center self-center mb-2 md:self-end">
                        <x-primary-info-button href="{{ route('admin.support.tickets') }}" wire:navigate>
                            Back To Tickets
                        </x-primary-info-button>
                    </div>
                </div>
        </div>

        <div class="pt-4 border-t border-neutral-200 dark:border-neutral-600">
            <dl class="text-sm divide-y divide-neutral-200 dark:divide-neutral-600">
                <div class="grid grid-cols-2 gap-4 py-3">
                    <span class="text-neutral-600 dark:text-neutral-400">Ticket Submitted</span>
                    <span class="text-xs text-neutral-800 dark:text-neutral-200 md:text-small">{{ $ticket->created_at->format('d/m/Yh:i:s A') }} - {{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                @isset($ticket->closed_at)
                <div class="grid grid-cols-2 gap-4 py-3">
                    <span class="text-neutral-600 dark:text-neutral-400">Closed</span>
                    <span class="text-xs text-neutral-800 dark:text-neutral-200 md:text-small">{{ $ticket->closed_at?->format('d/m/Yh:i:s A') }} - {{ $ticket->closed_at?->diffForHumans() }}</span>
                </div>
                @endisset
            </dl>
        </div>


        <div class="pt-6 border-t border-neutral-200 dark:border-neutral-600">
            <h3 class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-200">Conversation</h3>
            <livewire:components.support.chat-component :ticket="$ticket" />
        </div>
</div>

