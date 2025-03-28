<?php

namespace App\Livewire\Pages\Admin\Support;

use App\Mail\SupportResponseMail;
use App\Models\Admin\SupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class TicketDetail extends Component
{
    use LivewireAlert;

    public $ticket;
    public $user_subscription;

    public function mount(SupportTicket $ticket)
    {
        $this->ticket = $ticket;

        // Add subscription data
        $user = $ticket->user;
        $subscription = $user->lastSubscription();

        if ($subscription) {
            $subscription->started_at = $subscription->started_at->toDateTimeString();
            $subscription->expired_at = $subscription->expired_at->toDateTimeString();
            $subscription->remaining_time = Carbon::parse($subscription->expired_at)->diffForHumans(Carbon::now(), [
                'parts' => 3,
                'join' => true,
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            ]);
            $this->user_subscription = $subscription;
        }
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



    public function updateStatus($status)
    {
        $updateData = ['status' => $status];

        if ($status == 'closed') {
            $updateData['closed_at'] = now();
        }else{
            $updateData['closed_at'] = null;
        }

        $this->ticket->update($updateData);

        $this->alert('success', 'Ticket status updated successfully.', ['position' => 'bottom-end']);
    }


    public function render()
    {
        return view('livewire.pages.admin.support.ticket-detail', [
            'ticket' => $this->ticket
        ])->layout('layouts.app', ['title' => 'Ticket Detail']);
    }
}
