<?php

namespace App\Livewire\Pages\Admin\Server;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignServer;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;
class ServerForm extends Component
{
    use LivewireAlert;

    public $server_id;
    public $name = '';
    public $assigned_to_user_id = null;
    public $current_quota = 0;
    public $admin_notes = '';
    public $userSearch = '';
    public $last_access_time = null;
    public $previous_user_id = null; // To track user changes


    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'without_space',
                Rule::unique('servers')->where(function ($query) {
                    return $query->where('assigned_to_user_id', $this->assigned_to_user_id);
                })->ignore($this->server_id),
            ],
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'admin_notes' => 'nullable|string',
        ];
    }

    public function mount($server = null)
    {
        if ($server) {
            $this->server_id = $server;
            $serverModel = Server::with('assignedUser')->findOrFail($server);
            $this->fill($serverModel->toArray());
            $this->assigned_to_user_id = $serverModel->assigned_to_user_id;
            $this->previous_user_id = $serverModel->assigned_to_user_id;

        }
    }

    public function getUsersProperty()
    {
        $query = User::role('User');

        if ($this->userSearch) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('username', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->userSearch . '%');
            })->limit(30);
        } else {
            $query->limit(30);
        }

        return $query->get();
    }


public function saveServer()
{
    $validatedData = $this->validate();

    try {
        DB::beginTransaction();

        if ($this->server_id) {
            $server = Server::findOrFail($this->server_id);

            // If user assignment has changed
            if ($this->previous_user_id !== $validatedData['assigned_to_user_id']) {
                // Get all campaigns using this server for the previous user
                $affectedCampaigns = Campaign::whereHas('servers', function($query) {
                    $query->where('server_id', $this->server_id);
                })
                ->where('user_id', $this->previous_user_id)
                ->where('is_active', true)
                ->get();

                // Deactivate affected campaigns and remove server
                foreach ($affectedCampaigns as $campaign) {
                    // Deactivate campaign
                    $campaign->update(['is_active' => false]);

                    // Remove server from campaign
                    $campaign->servers()->detach($this->server_id);

                    // Session::flash('info', 'Server has been removed from previous user\'s campaigns and campaigns have been deactivated.');
                }
            }

            $server->update($validatedData);
        } else {
            Server::create($validatedData);
        }

        DB::commit();
        Session::flash('success', 'Server saved successfully.');
        return $this->redirect(route('admin.servers'), navigate: true);

    } catch (\Exception $e) {
        DB::rollBack();
        $this->alert('error', 'Failed to save server: ' . $e->getMessage(), ['position' => 'bottom-end']);
    }
}
    public function updatedAssignedToUserId($value)
    {
        // If this is an edit and the user is being changed
        if ($this->server_id && $this->previous_user_id !== $value) {
            // Get count of campaigns using this server for the previous user
            $campaignCount = CampaignServer::whereHas('campaign', function($query) {
                $query->where('user_id', $this->previous_user_id);
            })->where('server_id', $this->server_id)->count();

            if ($campaignCount > 0) {
                $this->alert('warning',
                    "This server is currently used in  campaign by the previous user. " .
                    "Changing the assignment will remove it from those campaigns.",
                    ['position' => 'bottom-end', 'timeout' => 7000]
                );
            }
        }
    }
    public function render()
    {
        return view('livewire.pages.admin.server.server-form', [
            'users' => $this->users,
        ])->layout('layouts.app', ['title' => $this->server_id ? 'Edit Server' : 'New Server']);
    }
}
