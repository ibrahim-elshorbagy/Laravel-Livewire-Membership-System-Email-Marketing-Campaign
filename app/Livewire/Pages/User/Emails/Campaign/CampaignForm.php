<?php

namespace App\Livewire\Pages\User\Emails\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Email\EmailMessage;
use App\Models\EmailListName;
use App\Models\Server;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class CampaignForm extends Component
{
    use LivewireAlert;

    public $campaign_id;
    public $title = '';
    public $message_id = null;
    public $selectedServers = [];
    public $selectedLists = [];
    public $selectAllServers = false;
    public $selectAllLists = false;

    // Search inputs
    public $messageSearch = '';
    public $serverSearch = '';
    public $listSearch = '';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'message_id' => 'nullable|exists:email_messages,id',
            'selectedServers' => 'nullable|array',
            'selectedServers.*' => 'exists:servers,id',
            'selectedLists' => 'nullable|array',
            'selectedLists.*' => 'exists:email_list_names,id',
        ];
    }

    public function mount($campaign = null)
    {
        if ($campaign) {
            $this->campaign_id = $campaign;
            $campaignModel = Campaign::with(['servers', 'emailLists'])->findOrFail($campaign);

            if ($campaignModel->user_id != Auth::id() || $campaignModel->status === 'Completed') {
                return redirect()->route('user.campaigns.list');
            }

            $this->title = $campaignModel->title;
            $this->message_id = $campaignModel->message_id;
            $this->selectedServers = $campaignModel->servers->pluck('id')->toArray();
            $this->selectedLists = $campaignModel->emailLists->pluck('id')->toArray();
        }
    }

    public function updatedSelectAllServers($value)
    {
        if ($value) {
            $this->selectedServers = $this->availableServers
                ->filter(function($server) {
                    return !$server->is_used;
                })
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedServers = [];
        }
    }

    public function updatedSelectAllLists($value)
    {
        if ($value) {
            $this->selectedLists = $this->availableLists->pluck('id')->toArray();
        } else {
            $this->selectedLists = [];
        }
    }

    public function getAvailableServersProperty()
    {
        return Server::where('assigned_to_user_id', Auth::id())
            ->when($this->serverSearch, function($query) {
                $query->where('name', 'like', '%' . $this->serverSearch . '%');
            })
            ->get()
            ->map(function($server) {
                $server->is_used = $server->campaignServers()
                    ->where('campaign_id', '!=', $this->campaign_id ?? 0)
                    ->exists();
                return $server;
            });
    }

    public function getAvailableListsProperty()
    {
        return EmailListName::where('user_id', Auth::id())
            ->when($this->listSearch, function($query) {
                $query->where('name', 'like', '%' . $this->listSearch . '%');
            })
            ->get();
    }

    public function getAvailableMessagesProperty()
    {
        return EmailMessage::where('user_id', Auth::id())
            ->select('id', 'message_title', 'email_subject')
            ->when($this->messageSearch, function($query) {
                $query->where(function($q) {
                    $q->where('message_title', 'like', '%' . $this->messageSearch . '%')
                    ->orWhere('email_subject', 'like', '%' . $this->messageSearch . '%');
                });
            })
            ->get();
    }

    public function saveCampaign()
    {
        $this->validate();

        // Check if any selected servers are already in use
        $usedServers = Server::whereIn('id', $this->selectedServers)
            ->get()
            ->filter(function($server) {
                return $server->campaignServers()
                    ->where('campaign_id', '!=', $this->campaign_id ?? 0)
                    ->exists();
            });

        if ($usedServers->isNotEmpty()) {
            $this->alert('error', 'Some selected servers are already in use by other campaigns.', ['position' => 'bottom-end']);
            return;
        }

        try {
            DB::beginTransaction();

            $campaignData = [
                'user_id' => Auth::id(),
                'title' => $this->title,
                'message_id' => $this->message_id,
            ];

            if ($this->campaign_id) {
                $campaign = Campaign::findOrFail($this->campaign_id);
                $campaign->update($campaignData);
            } else {
                $campaign = Campaign::create($campaignData);
            }

            // Sync relationships
            $campaign->servers()->sync($this->selectedServers);
            $campaign->emailLists()->sync($this->selectedLists);

            // Simple check: if campaign is sending and has no servers or lists, pause it
            if ($campaign->status === 'Sending' &&
                (empty($this->selectedServers) || empty($this->selectedLists))) {
                $campaign->update(['status' => 'Pause']);
            }
            DB::commit();

            Session::flash('success', 'Campaign saved successfully.');

            return redirect()->route('user.campaigns.list');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to save campaign: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign.campaign-form', [
            'availableServers' => $this->availableServers,
            'availableMessages' => $this->availableMessages,
            'availableLists' => $this->availableLists,
        ])->layout('layouts.app', ['title' => $this->campaign_id ? 'Edit Campaign' : 'New Campaign']);
    }
}
