<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Transaction extends Component
{
    use WithPagination;

    public $selectedTab = 'all';
    public $user;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function updatedSelectedTab()
    {
        $this->resetPage();
    }

    public function getPaymentsProperty()
    {
        return Payment::where('user_id', $this->user->id)
            ->when($this->selectedTab === 'approved', fn($query) => $query->where('status', 'approved'))
            ->when($this->selectedTab === 'pending', fn($query) => $query->where('status', 'pending'))
            ->when($this->selectedTab === 'failed', fn($query) => $query->whereIn('status', ['failed', 'cancelled']))
            ->with(['plan', 'subscription'])
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.pages.user.subscription.transaction', [
            'payments' => $this->payments,
        ])->layout('layouts.app');
    }
}
