<?php

namespace App\Livewire\Pages\Admin\Subscription;

use Livewire\Component;
use App\Models\Subscription\Note;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class SubscriptionNote extends Component
{
    use LivewireAlert;

    public $subscription;
    public $note;
    public $content;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($subscription)
    {
        $this->subscription = $subscription;
        $this->note = Note::firstOrCreate(
            ['subscription_id' => $subscription->id],
            ['content' => '']
        );
        $this->content = $this->note->content;
    }

    public function updateNote()
    {
        $this->note->update([
            'content' => $this->content
        ]);

        $this->alert('success', 'Note updated successfully', ['position' => 'bottom-end']);
        $this->dispatch('refreshComponent');
    }


    public function render()
    {
        return view('livewire.pages.admin.subscription.subscription-note');
    }
}
