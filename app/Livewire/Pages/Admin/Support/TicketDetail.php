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
            $this->ticket->user_subscription = $subscription;
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






    public function sendResponse()
    {
        $this->validate();

        // Update ticket with response
        $cleanResponse = Purifier::clean($this->response);

        $processedMessage = $this->processEmailImages($cleanResponse);

        $this->ticket->update([
            'admin_response' => $cleanResponse,
            'status' => 'closed',
            'responded_at'=> now(),
            'closed_at' => now()
        ]);

        // Send email to user
        $mailData = [
            'name' => $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name,
            'email' => $this->ticket->user->email,
            'subject' => 'Re: ' . $this->ticket->subject,
            'message' => $processedMessage['message'],
            'attachments' => $processedMessage['attachments']
        ];

        Mail::to($this->ticket->user->email)->queue(new SupportResponseMail($mailData));


        // Reset form and show success message
        $this->reset('response');
        $this->alert('success', 'Response sent successfully.', ['position' => 'bottom-end']);
    }

    public function render()
    {
        return view('livewire.pages.admin.support.ticket-detail', [
            'ticket' => $this->ticket
        ])->layout('layouts.app', ['title' => 'Ticket Detail']);
    }
}
