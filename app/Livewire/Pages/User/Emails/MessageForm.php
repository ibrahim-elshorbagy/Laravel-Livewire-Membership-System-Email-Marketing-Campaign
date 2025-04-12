<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use App\Models\Email\EmailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Rules\ProhibitedWords;
use Mews\Purifier\Facades\Purifier;
use App\Services\HtmlPurifierService;
use HTMLPurifier;

class MessageForm extends Component
{
    use LivewireAlert;

    public $message_id;
    public $message_title = '';
    public $email_subject = '';
    public $message_html = '';
    public $message_plain_text = '';
    public $sender_name = '';
    public $reply_to_email = '';
    public $sending_status = 'PAUSE';
    public $showPreview = false;

    public function rules(): array
    {
        $messageHtmlRules = ['nullable', 'string'];
        $messagePlainTextRules = ['nullable', 'string'];

        if (!(auth()->user()->hasRole('admin') || auth()->user()->can('allow-prohibited-words'))) {
            $messageHtmlRules[] = new ProhibitedWords();
            $messagePlainTextRules[] = new ProhibitedWords();
        }

        return [
            'message_title' => ['required', 'string','max:255'],
            'email_subject' => ['required', 'string','max:255'],
            'sender_name' => ['nullable', 'string','max:255'],
            'reply_to_email' => ['nullable', 'email','max:255'],
            'sending_status' => ['in:RUN,PAUSE'],
            'message_html'  => $messageHtmlRules,
            'message_plain_text' => $messagePlainTextRules,
        ];
    }


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

        $cleanMessage = Purifier::clean($validatedData['message_html'], 'youtube');
        $validatedData['message_html'] = $cleanMessage;

        // $htmlPurifier = new HtmlPurifierService();
        // $validatedData['message_html'] = $htmlPurifier->purifyFullHtml($validatedData['message_html']);






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
