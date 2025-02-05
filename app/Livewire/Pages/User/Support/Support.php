<?php

namespace App\Livewire\Pages\User\Support;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportMessage;
use App\Models\User;
use App\Mail\SupportMail;

use Jantinnerezo\LivewireAlert\LivewireAlert;

class Support extends Component
{
    use LivewireAlert;

    public $name;
    public $email;
    public $subject;
    public $message;

    public function mount()
    {
        // Get current user info
        $this->name = auth()->user()->first_name . ' ' . auth()->user()->last_name;
        $this->email = auth()->user()->email;
    }

    protected $rules = [
        'subject' => 'required|min:3',
        'message' => 'required|min:10',
    ];

    public function sendSupportMessage()
    {
        $this->validate();

        // Get admin email from settings
        $admin = User::find(1);
        $adminEmail = $admin->email;

        // Prepare mail data
        $mailData = [
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message
        ];

        // Send mail
        Mail::to($admin->email)->queue(new SupportMail($mailData));


        // Reset form
        $this->reset(['subject', 'message']);

        // Show success message
        $this->alert('success', 'Message sent successfully.',['position' => 'bottom-end']);

    }

    public function render()
    {
        return view('livewire.pages.user.support.support')->layout('layouts.app',['title' => 'Support']);;
    }
}
