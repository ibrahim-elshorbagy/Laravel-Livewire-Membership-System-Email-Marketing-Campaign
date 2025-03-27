<?php

namespace App\Livewire\Pages\User\Support;

use App\Models\Admin\SupportTicket;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mews\Purifier\Facades\Purifier;

class TicketDetail extends Component
{
    use LivewireAlert;

    public $ticket;

    public function mount(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }
        $this->ticket = $ticket;
    }

    public function render()
    {
        return view('livewire.pages.user.support.ticket-detail', [
            'ticket' => $this->ticket
        ])->layout('layouts.app', ['title' => 'Support Ticket Detail']);
    }
}