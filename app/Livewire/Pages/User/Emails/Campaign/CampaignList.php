<?php

namespace App\Livewire\Pages\User\Emails\Campaign;

use App\Models\Campaign\Campaign;
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

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:title,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
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

        if (!$campaign->canBeActive()) {
            $message = [];
            if ($campaign->servers()->count() === 0) {
                $message[] = "No servers assigned";
            }
            if ($campaign->emailLists()->count() === 0) {
                $message[] = "No email lists assigned";
            }

            $this->alert('error', 'Campaign cannot be activated: ' . implode(' and ', $message), [
                'position' => 'bottom-end',
                'timer' => 5000
            ]);
            return;
        }

        $campaign->update(['is_active' => !$campaign->is_active]);

        $this->alert('success',
            $campaign->is_active ? 'Campaign activated successfully!' : 'Campaign deactivated successfully!',
            ['position' => 'bottom-end']
        );
    }
    
    public function getCampaignsProperty()
    {
        return Campaign::with(['message', 'servers', 'emailLists'])
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhereHas('message', function($messageQuery) {
                          $messageQuery->where('message_title', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign.campaign-list', [
            'campaigns' => $this->campaigns,
        ])->layout('layouts.app', ['title' => 'Campaigns']);
    }
}
