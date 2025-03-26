<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Transaction extends Component
{
    use WithPagination;

    public $selectedTab = 'all';

    public function updatedSelectedTab()
    {
        $this->validateOnly('selectedTab', [
            'selectedTab' => 'in:all,approved,pending,refunded,failed,cancelled',
        ]);

        $this->resetPage();
    }

    public function getPaymentsProperty()
    {
        return Payment::where('user_id', Auth::id())
            ->when($this->selectedTab === 'approved', fn($query) => $query->where('status', 'approved'))
            ->when($this->selectedTab === 'pending', fn($query) => $query->where('status', 'pending'))
            ->when($this->selectedTab === 'refunded', fn($query) => $query->where('status', 'refunded'))
            ->when($this->selectedTab === 'failed', fn($query) => $query->where('status', 'failed'))
            ->when($this->selectedTab === 'cancelled', fn($query) => $query->where('status','cancelled'))
            ->with(['plan', 'subscription'])
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.pages.user.subscription.transaction', [
            'payments' => $this->payments,
        ])->layout('layouts.app',['title' => 'My Transactions']);
    }
}
