<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use App\Models\Email\EmailCampaign;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class CampaignForm extends Component
{
    use LivewireAlert;

    public $campaign_title = '';
    public $email_subject = '';
    public $message_html = '';
    public $message_plain_text = '';
    public $sender_name = '';
    public $reply_to_email = '';
    public $sending_status = 'PAUSE';

    protected $rules = [
        'campaign_title' => 'required|string|max:255',
        'email_subject' => 'required|string|max:255',
        'message_html' => 'required|string',
        'message_plain_text' => 'required|string',
        'sender_name' => 'nullable|string|max:255',
        'reply_to_email' => 'nullable|email',
        'sending_status' => 'in:RUN,PAUSE'
    ];

    public function mount()
    {
        $existingCampaign = Auth::user()->emailCampaigns()->first();

        if ($existingCampaign) {
            $this->fill($existingCampaign->toArray());
        }
    }

    public function saveCampaign()
    {
        $validatedData = $this->validate();

        try {
            EmailCampaign::updateOrCreate(
                ['user_id' => Auth::id()],
                array_merge($validatedData, ['user_id' => Auth::id()])
            );

            $this->alert('success', 'Campaign saved successfully!');
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save campaign: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign-form')
            ->layout('layouts.app', ['title' => 'Email Campaign']);
    }
}
