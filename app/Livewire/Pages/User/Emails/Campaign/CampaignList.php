<?php

namespace App\Livewire\Pages\User\Emails\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\EmailListName;
use App\Models\Server;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Auth;

class CampaignList extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:title,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'statusFilter' => 'nullable|string|in:Sending,Pause,Completed',
        ];
    }

    public function deleteCampaign($campaignId)
    {
        try {
            $campaign = Campaign::where('user_id', Auth::id())
                ->findOrFail($campaignId);
            $campaign->delete();
            $this->alert('success', 'Campaign deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete campaign: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function toggleActive($campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        if (!$campaign->canBeModified()) {
            $this->alert('error', 'Completed campaigns cannot be modified', [
                'position' => 'bottom-end'
            ]);
            return;
        }

        // Only check when trying to activate
        if ($campaign->status === Campaign::STATUS_PAUSE) {
            $messages = [];
            if ($campaign->servers()->count() === 0) {
                $messages[] = "No servers assigned";
            }
            if ($campaign->emailLists()->count() === 0) {
                $messages[] = "No email lists assigned";
            }

            if (!empty($messages)) {
                $this->alert('error', 'Campaign cannot be activated: ' . implode(' and ', $messages), [
                    'position' => 'bottom-end'
                ]);
                return;
            }

            $campaign->update(['status' => Campaign::STATUS_SENDING]);
            $message = 'Campaign started successfully!';
        } else {
            $campaign->update(['status' => Campaign::STATUS_PAUSE]);
            $message = 'Campaign paused successfully!';
        }

        $this->alert('success', $message, ['position' => 'bottom-end']);
    }

    public function getCampaignsProperty()
    {
        return Campaign::with(['repeater:id,campaign_id,next_run_at','message:id,message_title,email_subject', 'servers:id,name', 'emailLists'])
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhereHas('message', function($messageQuery) {
                          $messageQuery->where('message_title', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getAvailableServersProperty()
    {
        return Server::where('assigned_to_user_id', Auth::id())
            ->whereDoesntHave('campaignServers')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign.campaign-list', [
            'campaigns' => $this->campaigns,
            'availableServers' => $this->availableServers,
        ])->layout('layouts.app', ['title' => 'Campaigns']);
    }
}
