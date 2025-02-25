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
                    // Base query to get campaigns using this server
                    $query = Campaign::whereHas('servers', function($query) {
                        $query->where('server_id', $this->server_id);
                    });

                    // If setting to null, get all active campaigns
                    // If reassigning to another user, get only previous user's campaigns
                    if ($validatedData['assigned_to_user_id'] !== null) {
                        $query->where('user_id', $this->previous_user_id);
                    }

                    $affectedCampaigns = $query->get();

                    // Deactivate affected campaigns and remove server
                    foreach ($affectedCampaigns as $campaign) {
                        if ($campaign->status === Campaign::STATUS_SENDING) {
                            $campaign->update(['status' => Campaign::STATUS_PAUSE]);
                        }
                        $campaign->servers()->detach($this->server_id);
                    }

                    if ($affectedCampaigns->count() > 0) {
                        Session::flash('info', 'Server has been removed from affected campaigns and they have been deactivated.');
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
            // Base query to get count of campaigns using this server
            $query = Campaign::whereHas('servers', function($query) {
                $query->where('server_id', $this->server_id);
            });

            // If setting to null, count all active campaigns
            // If reassigning to another user, count only previous user's campaigns
            if ($value !== null) {
                $query->where('user_id', $this->previous_user_id);
            }

            $campaignCount = $query->count();

            if ($campaignCount > 0) {
                $message = $value === null
                    ? "This server is currently used in active campaigns. Removing assignment will remove it from campaign."
                    : "This server is currently used in active campaigns by the previous user. Changing the assignment will remove it from user campaign.";

                $this->alert('warning', $message, [
                    'position' => 'bottom-end',
                    'timeout' => 7000
                ]);
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
