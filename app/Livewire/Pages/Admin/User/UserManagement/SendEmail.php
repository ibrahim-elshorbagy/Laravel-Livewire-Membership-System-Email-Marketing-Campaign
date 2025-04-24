<?php

namespace App\Livewire\Pages\Admin\User\UserManagement;

use App\Mail\AdminSendMail;
use Livewire\Component;
use App\Models\User;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class SendEmail extends Component
{
    use LivewireAlert;

    public User $user;
    public SystemEmail $emailTemplate;
    public $email_subject;
    public $message_html;
    public $activeEditor = 'advanced'; // 'advanced' for TinyMCE, 'code' for Code Editor

    public function mount(User $user, SystemEmail $email)
    {
        $this->user = $user;
        $this->email_subject = $email->email_subject;
        $this->message_html = $email->message_html;
    }

    public function sendEmail()
    {
        $data = $this->validate([
            'email_subject' => 'required|string|max:255',
            'message_html' => 'required|string'
        ]);

        try {

            $user = $this->user;

            $mailData = [
                'email_subject' => $data['email_subject'],
                'message_html' => html_entity_decode($this->message_html),
                'user_id' => $user->id,
                'subscription_id' => $user->subscription->id ?? null,
            ];

            Mail::to($user->email)->queue(new AdminSendMail($mailData));

            Session::flash('success', 'Email Sent successfully.');
            return $this->redirect(route('admin.users'), navigate: true);


        } catch (\Exception $e) {
            $this->alert('error', 'Failed to send email: ' . $e->getMessage(), ['position' => 'bottom-end']);

        }
    }

    public function render()
    {
        return view('livewire.pages.admin.user.user-management.send-email')
            ->layout('layouts.app',['title' => 'Send Email']);
    }
}
