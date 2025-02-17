<?php

namespace App\Livewire\Pages\Admin\Server;

use App\Models\Server;
use App\Models\User;
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
            Server::whereIn('id', $this->selectedServers)->delete();
            $this->selectedServers = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected servers deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete servers: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function getServersProperty()
    {
        return Server::with('assignedUser')
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getUsersProperty()
    {
        return User::when($this->userSearch, function ($query) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->userSearch . '%')
                ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                ->orWhere('email', 'like', '%' . $this->userSearch . '%');
            });
        })->get();
    }

    public function render()
    {
        return view('livewire.pages.admin.server.server-list', [
            'servers' => $this->servers,
            'users' => $this->users,
        ])->layout('layouts.app', ['title' => 'Servers']);
    }
}
