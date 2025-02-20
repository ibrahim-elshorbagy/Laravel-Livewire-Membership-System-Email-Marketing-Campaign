<?php

namespace App\Livewire\Pages\User\Emails\Partials;

use App\Models\Email\EmailMessage;
use Livewire\Component;

class ShowMessage extends Component
{
    public $message;

    public function mount($message)
    {
        $this->message = EmailMessage::findOrFail($message);
    }
    public function render()
    {
        return view('livewire.pages.user.emails.partials.show-message')
        ->layout('layouts.app', ['title' => 'View Message']);
    }
}
