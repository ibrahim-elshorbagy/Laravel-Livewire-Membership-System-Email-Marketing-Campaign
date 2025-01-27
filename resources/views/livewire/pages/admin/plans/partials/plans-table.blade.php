@props(['plans', 'periodType'])

<div>
    <!-- Table Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $periodType }} Plans
        </h3>
        {{-- <x-primary-create-button   wire:navigate>
            Create New Plan
        </x-primary-create-button> --}}
    </div>

    <!-- Table -->
    <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
        <thead class="text-sm bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
            <tr>
                <th scope="col" class="p-4">Plan Name</th>
                <th scope="col" class="p-4">Price (USD)</th>
                <th scope="col" class="p-4">Features</th>
                <th scope="col" class="p-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
            @foreach($plans as $plan)
            <tr>
                <td class="p-4">
                    <div class="font-medium text-neutral-900 dark:text-neutral-100">
                        {{ $plan->name }}
                    </div>
                </td>
                <td class="p-4">
                    ${{ number_format($plan->price, 2) }}
                </td>
                <td class="p-4">
                    <div class="space-y-1">
                        @foreach($plan->features as $feature)
                        <div class="flex items-center gap-2">
                            <span class="text-green-500">
                                <i class="fas fa-check"></i>
                            </span>
                            <span>
                                {{ $feature->name }}
                                @if($feature->pivot->charges)
                                ({{ $feature->pivot->charges }} {{ Str::plural($feature->name, $feature->pivot->charges)
                                }})
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </td>
                <td class="p-4">
                    <div class="flex space-x-2">
                        <x-primary-info-button  href="{{ route('admin.plans.edit', $plan) }}" wire:navigate>
                            <i class="fa-solid fa-pen-to-square"></i>
                        </x-primary-info-button>

                        {{-- <x-primary-danger-button wire:click="deletePlan({{ $plan->id }})"
                            onclick="confirm('Are you sure you want to delete this plan?') || event.stopImmediatePropagation()">
                            <i class="fa-solid fa-trash"></i>
                        </x-primary-danger-button> --}}
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
