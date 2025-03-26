<?php

namespace App\Livewire\Pages\Admin\User\UserManagement;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use LucasDotVin\Soulbscription\Models\Plan;
use Illuminate\Support\Str;

class Edit extends Component
{
    public User $user;
    public $email;
    public $first_name;
    public $last_name;
    public $username;
    public $company;
    public $country;
    public $whatsapp;
    public $active;
    public $password;
    public $password_confirmation;
    public $image_url;
    public $selectedRole;
    public $permissions = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->email = $user->email;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->username = $user->username;
        $this->company = $user->company;
        $this->country = $user->country;
        $this->whatsapp = $user->whatsapp;
        $this->active = $user->active;
        $this->selectedRole = $user->roles->first()?->name ?? '';
        $this->permissions = $user->permissions->pluck('name')->toArray();
    }

    protected function rules()
    {
        return [
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'username' => ['required', 'min:3', Rule::unique('users')->ignore($this->user->id)],
            'company' => 'nullable|string',
            'country' => 'nullable|string',
            'whatsapp' => 'nullable|string|regex:/^\+?\d{10,13}$/',
            'active' => 'boolean',
            'password' => 'nullable|min:8|confirmed',
            'selectedRole' => 'required',
            'permissions' => 'array'
        ];
    }

    protected $messages = [
        'selectedRole.required' => 'Please select a role for the user.',
    ];

    public function formatPermissionName($permission)
    {
        return Str::title(str_replace('-', ' ', $permission));
    }

    public function updateUser()
    {
        $this->validate();

        $data = [
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'company' => $this->company,
            'country' => $this->country,
            'whatsapp' => $this->whatsapp,
            'active' => $this->active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
        $oldRole = $this->user->roles->first()?->name;

        $this->user->update($data);

        // Update role if changed
        if ($oldRole !== $this->selectedRole) {
            $this->user->syncRoles([$this->selectedRole]);

            // If changing to user role and user has no active subscription
            if ($this->selectedRole === 'user' && !$this->user->lastSubscription()) {
                $trialPlan = Plan::find(1);
                if ($trialPlan) {
                    $this->user->graceSubscribeTo($trialPlan);
                }
            }
        }

        // Update permissions
        $this->user->syncPermissions($this->permissions);

        Session::flash('success', 'User updated successfully.');
        return $this->redirect(route('admin.users'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.admin.user.user-management.edit', [
            'roles' => Role::where('name', '!=', 'super-admin')->get(),
            'allPermissions' => Permission::all()
        ])->layout('layouts.app',['title' => 'Edit User']);
    }
}
