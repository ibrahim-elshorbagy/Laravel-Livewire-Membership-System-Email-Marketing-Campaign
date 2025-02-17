<?php

namespace App\Livewire\Pages\Admin\Server;

use App\Models\Server;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;

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


    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            // 'current_quota' => 'required|integer|min:0',
            'admin_notes' => 'nullable|string',
            // 'last_access_time' => 'nullable|date'
        ];
    }

    public function mount($server = null)
    {
        if ($server) {
            $this->server_id = $server;
            $serverModel = Server::with('assignedUser')->findOrFail($server);
            $this->fill($serverModel->toArray());
            $this->assigned_to_user_id = $serverModel->assigned_to_user_id;
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
            if ($this->server_id) {
                Server::where('id', $this->server_id)->update($validatedData);
            } else {
                Server::create($validatedData);
            }

            Session::flash('success', 'Server saved successfully.');
            return $this->redirect(route('admin.servers'), navigate: true);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save server: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.server.server-form', [
            'users' => $this->users,
        ])->layout('layouts.app', ['title' => $this->server_id ? 'Edit Server' : 'New Server']);
    }
}
