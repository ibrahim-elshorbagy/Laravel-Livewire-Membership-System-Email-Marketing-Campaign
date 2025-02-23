<?php

namespace App\Livewire\Pages\User\Emails\Campaign;

use App\Jobs\DeleteHistoryRecords;
use App\Models\Campaign\EmailHistory;
use App\Models\Campaign\Campaign;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Auth;

class Progress extends Component
{
    use WithPagination, LivewireAlert;

    public $campaign;
    public $search = '';
    public $sortField = 'sent_time';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedRecords = [];
    public $selectPage = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'sent_time'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function deleteSelected()
    {
        try {
            EmailHistory::whereIn('id', $this->selectedRecords)
                ->where('campaign_id', $this->campaign->id)
                ->delete();

            $this->selectedRecords = [];
            $this->selectPage = false;

            $this->alert('success', 'Selected records deleted successfully!', [
                'position' => 'bottom-end',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete records!', [
                'position' => 'bottom-end',
            ]);
        }
    }

    public function deleteAll()
    {
        try {
            $count = EmailHistory::where('campaign_id', $this->campaign->id)->count();

            if ($count === 0) {
                $this->alert('info', 'No records to delete.', [
                    'position' => 'bottom-end'
                ]);
                return;
            }

            if ($count > 10000) {
                DeleteHistoryRecords::dispatch($this->campaign->id);

                $this->alert('success',
                    "Processing deletion of {$count} records. This may take a while.",
                    ['position' => 'bottom-end']
                );
            } else {
                EmailHistory::where('campaign_id', $this->campaign->id)->delete();

                $this->alert('success', "All records deleted successfully!", [
                    'position' => 'bottom-end'
                ]);
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete records!', [
                'position' => 'bottom-end'
            ]);
        }
    }

    public function deleteRecord($id)
    {
        try {
            EmailHistory::where('id', $id)
                ->where('campaign_id', $this->campaign->id)
                ->delete();

            $this->alert('success', 'Record deleted successfully!', [
                'position' => 'bottom-end',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete record!', [
                'position' => 'bottom-end',
            ]);
        }
    }

    public function getHistoryRecordsProperty()
    {
        return EmailHistory::select('id', 'email_id', 'campaign_id', 'status', 'sent_time')
            ->with(['email:id,email', 'campaign:id,title'])
            ->where('campaign_id', $this->campaign->id)
            ->when($this->search, function($query) {
                $query->whereHas('email', function($q) {
                    $q->where('email', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign.progress', [
            'historyRecords' => $this->historyRecords,
        ])->layout('layouts.app', ['title' => 'Campaign Progress']);
    }
}
