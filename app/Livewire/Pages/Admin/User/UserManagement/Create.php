<?php

namespace App\Livewire\Pages\Admin\User\UserManagement;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;
use LucasDotVin\Soulbscription\Models\Plan;
use Illuminate\Support\Str;

class Create extends Component
{
    use LivewireAlert;

    public $email;
    public $first_name;
    public $last_name;
    public $username;
    public $company;
    public $country;
    public $whatsapp;
    public $active = true;
    public $password;
    public $password_confirmation;
    public $selectedRole = '';
    public $permissions = [];

    protected $rules = [
        'email' => 'required|email|unique:users',
        'first_name' => 'required|min:2',
        'last_name' => 'required|min:2',
        'username' => 'required|unique:users|min:3',
        'company' => 'nullable|string',
        'country' => 'nullable|string',
        'whatsapp' => 'nullable|string|regex:/^\+?\d{10,13}$/',
        'password' => 'required|min:8|confirmed',
        'selectedRole' => 'required',
        'permissions' => 'array'
    ];

    protected $messages = [
        'selectedRole.required' => 'Please select a role for the user.',
    ];

    public function mount()
    {
        // Set default role if exists
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            $this->selectedRole = $defaultRole->name;
        }
    }

    public function formatPermissionName($permission)
    {
        return Str::title(str_replace('-', ' ', $permission));
    }

    public function createUser()
    {
        $this->validate();

        $user = User::create([
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'company' => $this->company,
            'country' => $this->country,
            'whatsapp' => $this->whatsapp,
            'active' => $this->active,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'
        ]);

        $user->assignRole($this->selectedRole);

        // Assign selected permissions
        if (!empty($this->permissions)) {
            $user->syncPermissions($this->permissions);
        }

        if ($this->selectedRole === 'user') {
            $trialPlan = Plan::find(1);
            if ($trialPlan) {
                $user->graceSubscribeTo($trialPlan);
            }
        }
        Session::flash('success', 'User created successfully.');

         return $this->redirect(route('admin.users'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.admin.user.user-management.create', [
            'roles' => Role::where('name', '!=', 'super-admin')->get(),
            'allPermissions' => Permission::all()
        ])->layout('layouts.app',['title' => 'Create User']);
    }
}
