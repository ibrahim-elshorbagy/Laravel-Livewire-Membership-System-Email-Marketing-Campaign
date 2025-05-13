<?php

namespace App\Livewire\Pages\User\Server;

use App\Models\Server;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ServerList extends Component
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
            'sortField' => 'required|in:name,last_access_time,current_quota,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
        ];
    }

    public function getServersProperty()
    {
        $servers = Server::with(['campaignServers.campaign', 'apiRequests' => function ($q) {
                $q->where('request_time', '>=', now()->subDay())->limit(1);
            }])
            ->where('assigned_to_user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('current_quota', 'like', '%' . $this->search . '%');
                });
            })
            ->when(true, function ($query) {
                $query->with(['apiRequests' => function ($q) {
                    $q->where('request_time', '>=', now()->subDay())->limit(1);
                }]);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Add orphan status after retrieving the results
        $servers->getCollection()->transform(function ($server) {
            // Check if there are no requests within the last 24 hours
            $server->is_orphan = $server->apiRequests->isEmpty();
            // Format campaign titles
            $server->campaign = $server->campaignServers->map(function($cs) {
                return $cs->campaign ? $cs->campaign->title : '';
            })->filter()->implode(', ');
            // Remove admin_notes
            unset($server->admin_notes);
            return $server;
        });

        // Return paginated results
        return $servers;
    }


    public function render()
    {
        return view('livewire.pages.user.server.server-list', [
            'servers' => $this->servers
        ])->layout('layouts.app', ['title' => 'My Sending bots']);
    }
}
