<?php

namespace App\Livewire\Pages\Admin\User\UserManagement;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use LucasDotVin\Soulbscription\Models\Plan;

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
        $this->image_url = $user->image_url ?? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
        $this->selectedRole = $user->roles->first()?->name ?? '';
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
            'image_url' => 'nullable|url',
            'selectedRole' => 'required',
        ];
    }

    protected $messages = [
        'selectedRole.required' => 'Please select a role for the user.',
    ];

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
            'image_url' => $this->image_url,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
        $oldRole = $this->user->roles->first()?->name;

        $this->user->update($data);

        // Update role if changed
        if ($oldRole !== $this->selectedRole) {$this->user->syncRoles([$this->selectedRole]);

        // If changing to user role and user has no active subscription
        if ($this->selectedRole === 'user' && !$this->user->lastSubscription()) {
            $trialPlan = Plan::where('name', 'Trial')->first();
            if ($trialPlan) {
                $this->user->subscribeTo($trialPlan);
            }
        }
    }

        Session::flash('success', 'User updated successfully.');
        return $this->redirect(route('admin.users'), navigate: true);

    }

    public function render()
    {
        return view('livewire.pages.admin.user.user-management.edit', [
            'roles' => Role::where('name', '!=', 'super-admin')->get()
        ])->layout('layouts.app');
    }
}
