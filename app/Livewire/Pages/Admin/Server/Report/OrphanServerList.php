<?php

namespace App\Livewire\Pages\Admin\Server\Report;

use App\Models\Campaign\Campaign;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class OrphanServerList extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedServers = [];
    public $selectPage = false;
    public $serverId;
    public $userSearch = '';
    public $selectedUserId = null;
    public $editingServerId = null;
    public $previousUserId = null;


    public $admin_notes = '';
    public $selectedServerId = null;
    public $edit_admin_notes = '';
    public $tempEmailsCount = null;


    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:name,last_access_time,current_quota,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectedServers' => 'array',
            'selectedServers.*' => 'integer|exists:servers,id',
            'selectPage' => 'boolean',
            'serverId' => 'nullable|integer|exists:servers,id',
            'selectedUserId' => 'nullable|exists:users,id',
        ];
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedServers = $this->servers->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedServers = [];
        }
    }

    public function updatingSearch()
    {
        $this->validateOnly('search');
        $this->resetPage();
    }


    public function saveNote()
    {
        $server = Server::findOrFail($this->selectedServerId);
        $server->update([
            'admin_notes' => $this->edit_admin_notes
        ]);

        $this->alert('success', 'Notes saved successfully!', ['position' => 'bottom-end']);
        $this->dispatch('close-modal', 'edit-note-modal');
    }

    public function saveEmailsCount($serverId)
    {
        $this->validate([
            'tempEmailsCount' => 'required|integer|min:1|max:255'
        ]);

        $server = Server::findOrFail($serverId);
        $server->update([
            'emails_count' => $this->tempEmailsCount
        ]);

        $this->tempEmailsCount = null;
        $this->alert('success', 'Emails count updated successfully!', ['position' => 'bottom-end']);
    }

    public function deleteServer($serverId)
    {
        try {
            Server::findOrFail($serverId)->delete();
            $this->alert('success', 'Server deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete server: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedServers' => 'required|array|min:1',
            'selectedServers.*' => 'integer|exists:servers,id'
        ]);

        try {
            // Get all selected servers
            $servers = Server::whereIn('id', $this->selectedServers)->get();

            foreach($servers as $server) {
                // This will trigger the observer and handle campaign deactivation
                $server->delete();
            }

            $this->selectedServers = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected servers deleted successfully!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete servers: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function deleteAllOrphanServers()
    {
        try {
            // Get all orphan servers
            $orphanServers = $this->getOrphanServersQuery()->get();

            foreach($orphanServers as $server) {
                $server->delete();
            }

            $this->alert('success', 'All orphan servers deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete orphan servers: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    protected function getOrphanServersQuery()
    {
        return Server::with('assignedUser')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('api_requests')
                    ->whereColumn('api_requests.serverid', 'servers.name')
                    ->where('request_time', '>=', now()->subDay());
            })
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('current_quota', 'like', '%' . $this->search . '%')
                    ->orWhereHas('assignedUser', function($userQuery) {
                        $userQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search . '%')
                                ->orWhere('username', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                });
            });
    }

    public function getServersProperty()
    {
        return $this->getOrphanServersQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getUsersProperty()
    {
        return User::role('User')
            ->whereHas('subscription', function($query) {
                $query->where(function($q) {
                    $q->where('expired_at', '>', now()) // active subscription
                    ->orWhere('grace_days_ended_at', '>', now()); // or within grace period
                })
                ->whereHas('plan', function($q) {
                    $q->where('id', '!=', 1); // exclude plan_id 1
                });
            })
            ->when($this->userSearch, function ($query) {
                $query->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->userSearch . '%')
                        ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                        ->orWhere('username', 'like', '%' . $this->userSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->userSearch . '%');
                });
            })
            ->limit(30)
            ->get();
    }

    public function assignUser($serverId, $userId)
    {
        try {
            DB::beginTransaction();

            $server = Server::findOrFail($serverId);
            $previousUserId = $server->assigned_to_user_id;

            if ($previousUserId !== $userId) {
                $query = Campaign::whereHas('servers', function($query) use ($serverId) {
                    $query->where('server_id', $serverId);
                });

                if ($userId !== null) {
                    $query->where('user_id', $previousUserId);
                }

                $affectedCampaigns = $query->get();

                foreach ($affectedCampaigns as $campaign) {
                    if ($campaign->status === Campaign::STATUS_SENDING) {
                        $campaign->update(['status' => Campaign::STATUS_PAUSE]);
                    }
                    $campaign->servers()->detach($serverId);
                }

                $server->update(['assigned_to_user_id' => $userId]);

                if ($affectedCampaigns->count() > 0) {
                    $this->alert('info', 'Server has been removed from affected campaigns and they have been deactivated.', ['position' => 'bottom-end']);
                }

                $this->alert('success', 'User assignment updated successfully!', ['position' => 'bottom-end']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to update user assignment: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.server.report.orphan-server-list', [
            'servers' => $this->servers,
            'users' => $this->users,
        ])->layout('layouts.app', ['title' => 'Orphan Sending bots']);
    }
}
