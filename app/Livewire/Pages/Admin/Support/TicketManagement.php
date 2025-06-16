<?php

namespace App\Livewire\Pages\Admin\Support;

use App\Models\Admin\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class TicketManagement extends Component
{
    use WithPagination, LivewireAlert;

    public $selectedTab = 'all';
    public $search = '';
    public $perPage = 10;
    public $userSearch = '';
    public $selectedTicketId = null;

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
            'selectedTicketId' => 'nullable|exists:support_tickets,id',
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

    public function impersonateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            session()->put('impersonated_by', auth()->id());
            auth()->login($user);
            return redirect()->route('dashboard');
        }
    }

    public function getTicketsProperty()
    {
        return SupportTicket::with(['user'])
            ->when($this->selectedTab != 'all', fn($query) => $query->where('status', $this->selectedTab))
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function($userQuery) {
                        $userQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search . '%')
                                ->orWhere('username', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function deleteTicket($ticketId)
    {
        $ticket = SupportTicket::find($ticketId);
        if ($ticket) {
            $ticket->delete();
            $this->alert('success', 'Ticket deleted successfully.');
        } else {
            $this->alert('error', 'Ticket not found.');
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.support.ticket-management', [
            'tickets' => $this->tickets,
        ])->layout('layouts.app', ['title' => 'Support Ticket Management']);
    }
}
