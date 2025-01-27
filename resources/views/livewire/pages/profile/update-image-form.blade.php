<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component
{
    use WithFileUploads;

    public $image;
    public $currentImage;

    public function mount()
    {
        $user = Auth::user();
        $this->currentImage = $user->image_url ?? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
    }

    public function updateImage(): void
    {
        $this->validate([
            'image' => ['required', 'image'],
        ]);

        $user = Auth::user();

        if ($user->image_url != null && $user->image_url != 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png') {
            Storage::disk('public')->deleteDirectory('User/' . $user->id . '/profile_image');
        }

        $path = $this->image->store('User/' . $user->id . '/profile_image', 'public');

        $user->update([
            'image_url' => Storage::url($path)
        ]);

        $this->currentImage = $user->image_url;
        $this->reset('image');

        $this->dispatch('image-updated');
        $this->dispatch('refresh-user-profile-display', [
        'image_url' => $user->image_url,
        'first_name' => $user->first_name,
        ]);
    }
}; ?>

<section>
    <header class="flex items-center gap-5">
        <i class="fa-solid fa-image fa-2xl"></i>
        <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Update Profile Image') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Update your profile image.') }}
            </p>
        </div>
    </header>

    <form wire:submit="updateImage" class="mt-6">
        <div class="mb-4">
            <img src="{{ $currentImage }}" class="object-cover w-32 h-32 rounded-lg">
        </div>

        <div class="space-y-4">
            <!-- File Input -->
            <div class="relative flex flex-col w-full max-w-sm gap-1 text-on-surface dark:text-on-surface-dark">
                <label for="fileInput" class="w-fit pl-0.5 text-sm">Upload File</label>
                <x-primary-upload-button id="fileInput" type="file" wire:model="image" accept="image/png,image/jpeg,image/webp" />
                {{-- <input id="fileInput" type="file" wire:model="image" accept="image/png,image/jpeg,image/webp"
                    class="w-full max-w-md text-sm overflow-clip rounded-radius bg-surface-alt/50 file:mr-4 file:border-none file:bg-surface-alt file:px-4 file:py-2 file:font-medium file:text-on-surface-strong focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-75 dark:bg-surface-dark-alt/50 dark:file:bg-surface-dark-alt dark:file:text-on-surface-dark-strong dark:focus-visible:outline-primary-dark" /> --}}
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <!-- Preview Image (if selected) -->
            @if ($image)
            <div class="mt-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Preview:</p>
                <img src="{{ $image->temporaryUrl() }}" class="object-cover w-32 h-32 mt-2 rounded-lg">
            </div>
            @endif

            <!-- Save Button - Only show when image is selected -->
            @if ($image)
                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            @endif
        </div>
    </form>
</section>
