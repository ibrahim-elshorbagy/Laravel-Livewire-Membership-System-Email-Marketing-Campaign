<?php

namespace App\Livewire\Pages\User\Support;

use App\Models\Admin\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class TicketList extends Component
{
    use WithPagination, LivewireAlert;

    public $selectedTab = 'all';
    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedTab' => ['except' => 'all'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'selectedTab' => 'required|in:all,open,in_progress,closed',
            'perPage' => 'required|integer|in:10,25,50',
        ];
    }

    public function updatedSelectedTab()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getTicketsProperty()
    {
        return SupportTicket::where('user_id', Auth::id())
            ->when($this->selectedTab !== 'all', fn($query) => $query->where('status', $this->selectedTab))
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.user.support.ticket-list', [
            'tickets' => $this->tickets,
        ])->layout('layouts.app', ['title' => 'My Support Tickets']);
    }
}