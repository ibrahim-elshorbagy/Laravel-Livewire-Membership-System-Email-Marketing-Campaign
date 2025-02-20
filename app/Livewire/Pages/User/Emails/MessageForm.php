<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use App\Models\Email\EmailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class MessageForm extends Component
{
    use LivewireAlert;

    public $message_id;
    public $email_subject = '';
    public $message_html = '';
    public $message_plain_text = '';
    public $showPreview = false;

    protected $rules = [
        'email_subject'       => 'required|string',
        'message_html'        => 'nullable|string',
        'message_plain_text'  => 'nullable|string',
    ];

    public function mount($message = null)
    {
        if ($message) {
            $this->message_id = $message;
            $messageModel = EmailMessage::findOrFail($message);
            $this->fill($messageModel->toArray());
        }
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function getPreviewContent()
    {
        return $this->message_html;
    }
    public function saveMessage()
    {
        $validatedData = $this->validate();

        try {
            if ($this->message_id) {
                EmailMessage::where('id', $this->message_id)->update($validatedData);
            } else {
                Auth::user()->emailMessages()->create($validatedData);
            }

            Session::flash('success', 'Emails saved successfully.');
            return $this->redirect(route('user.email-messages'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save message: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.message-form')
            ->layout('layouts.app', ['title' => $this->message_id ? 'Edit Message' : 'New Message']);
    }
}
