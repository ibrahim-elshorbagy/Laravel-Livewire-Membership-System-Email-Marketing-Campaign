<?php

namespace App\Livewire\Pages\Admin\Server;

use App\Models\Campaign\Campaign;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ServerList extends Component
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
    public $selectedEmailsCountServerId = null;

    // Bulk email count update properties
    public $bulkEmailsCount = null;
    public $showBulkEmailModal = false;


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
            'bulkEmailsCount' => 'nullable|integer|min:1|max:255',




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
        $this->validate([
            'selectedServerId' => 'required|exists:servers,id',
            'edit_admin_notes' => 'required|string',
        ]);

        $server = Server::findOrFail($this->selectedServerId);
        $server->update([
            'admin_notes' => $this->edit_admin_notes
        ]);

        $this->reset(['selectedServerId', 'edit_admin_notes']);

        $this->alert('success', 'Notes saved successfully!', ['position' => 'bottom-end']);
        $this->dispatch('close-modal', 'edit-note-modal');
    }


    public function saveEmailsCount()
    {
        $this->validate([
            'tempEmailsCount' => 'required|integer|min:1|max:255',
            'selectedEmailsCountServerId'=>'required|exists:servers,id'
        ]);

        $server = Server::findOrFail($this->selectedEmailsCountServerId);

        $server->update([
            'emails_count' => $this->tempEmailsCount
        ]);

        $this->reset(['selectedEmailsCountServerId', 'tempEmailsCount']);


        $this->tempEmailsCount = null;
        $this->alert('success', 'Emails count updated successfully!', ['position' => 'bottom-end']);
    }

    public function deleteEmail($serverId)
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

    public function getServersProperty()
    {
        $servers = Server::with('assignedUser')
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
            })
            ->when(true, function ($query) {
                $query->with(['apiRequests' => function ($q) {
                    $q->where('request_time', '>=', now()->subDay())->limit(1);
                }]);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Add orphan status to each server
        $servers->getCollection()->transform(function ($server) {
            // Check if there are no requests within the last 24 hours
            $server->is_orphan = $server->apiRequests->isEmpty(); // Check if no requests exist
            return $server;
        });

        return $servers;
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

            if ($previousUserId != $userId) {
                $query = Campaign::whereHas('servers', function($query) use ($serverId) {
                    $query->where('server_id', $serverId);
                });

                if ($userId != null) {
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

    public function bulkUpdateEmailsCount()
    {
        $this->validate([
            'selectedServers' => 'required|array|min:1',
            'selectedServers.*' => 'integer|exists:servers,id',
            'bulkEmailsCount' => 'required|integer',
        ]);

        try {
            Server::whereIn('id', $this->selectedServers)
                ->update(['emails_count' => $this->bulkEmailsCount]);

            $this->reset(['bulkEmailsCount']);
            $this->selectedServers = [];
            $this->selectPage = false;

            $this->alert('success', 'Emails count updated for selected servers successfully!', ['position' => 'bottom-end']);
            $this->dispatch('close-modal', 'bulk-emails-count-modal');

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update emails count: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.server.server-list', [
            'servers' => $this->servers,
            'users' => $this->users,
        ])->layout('layouts.app', ['title' => 'Sending bots']);
    }
}
