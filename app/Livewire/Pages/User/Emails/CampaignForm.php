<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use App\Models\Email\EmailCampaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class CampaignForm extends Component
{
    use LivewireAlert;

    public $campaign_id;
    public $campaign_title = '';
    public $email_subject = '';
    public $message_html = '';
    public $message_plain_text = '';
    public $sender_name = '';
    public $reply_to_email = '';
    public $sending_status = 'PAUSE';
    public $showPreview = false;

    protected $rules = [
        'campaign_title'      => 'required|string',
        'email_subject'       => 'required|string',
        'message_html'        => 'nullable|string',
        'message_plain_text'  => 'nullable|string',
        'sender_name'         => 'nullable|string',
        'reply_to_email'      => 'nullable|email',
        'sending_status'      => 'in:RUN,PAUSE'
    ];

    public function mount($campaign = null)
    {
        if ($campaign) {
            $this->campaign_id = $campaign;
            $campaignModel = EmailCampaign::findOrFail($campaign);
            $this->fill($campaignModel->toArray());
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
    public function saveCampaign()
    {
        $validatedData = $this->validate();

        try {
            if ($this->campaign_id) {
                EmailCampaign::where('id', $this->campaign_id)->update($validatedData);
            } else {
                Auth::user()->emailCampaigns()->create($validatedData);
            }

            Session::flash('success', 'Emails saved successfully.');
            return $this->redirect(route('user.email-campaigns'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save campaign: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign-form')
            ->layout('layouts.app', ['title' => $this->campaign_id ? 'Edit Campaign' : 'New Campaign']);
    }
}
