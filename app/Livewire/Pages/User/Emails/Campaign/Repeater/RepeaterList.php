<?php

namespace App\Livewire\Pages\User\Emails\Campaign\Repeater;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRepeater;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepeaterList extends Component
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

    public function toggleActive($repeaterId)
    {
        try {
            
            $repeater = CampaignRepeater::where('user_id', Auth::id())
                ->findOrFail($repeaterId);

            if ($repeater->completed_repeats >= $repeater->total_repeats) {
                $this->alert('error', 'This repeater has completed all runs and cannot be modified.', [
                    'position' => 'bottom-end'
                ]);
                return;
            }

            $repeater->update(['active' => !$repeater->active]);

            $message = $repeater->active
                ? 'Repeater activated successfully!'
                : 'Repeater paused successfully!';

            $this->alert('success', $message, ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update repeater: ' . $e->getMessage(), [
                'position' => 'bottom-end'
            ]);
        }
    }

    public function deleteRepeater($repeaterId)
    {
        try {
            $repeater = CampaignRepeater::where('user_id', Auth::id())
                ->findOrFail($repeaterId);
            $repeater->delete();
            $this->alert('success', 'Repeater deleted successfully!', [
                'position' => 'bottom-end'
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete repeater: ' . $e->getMessage(), [
                'position' => 'bottom-end'
            ]);
        }
    }

    public function getRepeatersProperty()
    {
        return CampaignRepeater::with(['campaign'])
            ->where('user_id', Auth::id())
            ->when($this->search, function($query) {
                $query->whereHas('campaign', function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function($query) {
                if ($this->statusFilter === 'Active') {
                    $query->where('active', true)
                        ->where('completed_repeats', '<', DB::raw('total_repeats'));
                } elseif ($this->statusFilter === 'Inactive') {
                    $query->where('active', false)
                        ->where('completed_repeats', '<', DB::raw('total_repeats'));
                } elseif ($this->statusFilter === 'Completed') {
                    $query->where('completed_repeats', '>=', DB::raw('total_repeats'));
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.user.emails.campaign.repeater.repeater-list', [
            'repeaters' => $this->repeaters,
            'title' => 'Campaign Repeaters',
        ])->layout('layouts.app', ['title' => 'Campaign Repeaters']);
    }
}
