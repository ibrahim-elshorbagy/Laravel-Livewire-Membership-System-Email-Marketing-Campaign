<?php

namespace App\Livewire\Pages\User\Emails\Partials;

use App\Models\Email\EmailMessage;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowMessage extends Component
{
    public $message;

    public function mount($message)
    {
        $this->message = EmailMessage::findOrFail($message);
        // Get user's unsubscribe information
        $userInfo = UserInfo::where('user_id', Auth::user()->id)->first();
        if ($userInfo && $userInfo->unsubscribe_status) {
            $unsubscribeHtml = '<hr><p style="text-align: center;">';
            $unsubscribeHtml .= $userInfo->unsubscribe_pre_text . ' ';

            // Check if the unsubscribe_link is an email or URL
            if (filter_var($userInfo->unsubscribe_link, FILTER_VALIDATE_EMAIL)) {
                // It's an email address
                $unsubscribeHtml .= '<a href="mailto:' . $userInfo->unsubscribe_link . '">' . $userInfo->unsubscribe_text . '</a>.';
            } else {
                // It's a URL
                $unsubscribeHtml .= '<a href="' . $userInfo->unsubscribe_link . '">' . $userInfo->unsubscribe_text . '</a>.';
            }

            $unsubscribeHtml .= '</p>';

            $this->message->message_html .= $unsubscribeHtml;

        }
    }
    public function render()
    {
        return view('livewire.pages.user.emails.partials.show-message')
        ->layout('layouts.app', ['title' => 'View Message']);
    }
}
