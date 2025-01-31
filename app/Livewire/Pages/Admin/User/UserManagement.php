<?php

namespace App\Livewire\Pages\Admin\User;

use App\Models\Payment\Payment;
use App\Models\Subscription\Note;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

class UserManagement extends Component
{
    use WithPagination;
    use LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $adminSearch = '';
    public $trashedSearch = ''; // New search for trashed users
    public $perPage = 10;
    public $selectedTab = 'users';

    protected $queryString = [
        'search' => ['except' => ''],
        'adminSearch' => ['except' => ''],
        'trashedSearch' => ['except' => ''],
        'selectedTab' => ['except' => 'users'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAdminSearch()
    {
        $this->resetPage();
    }

    public function updatingTrashedSearch()
    {
        $this->resetPage();
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            $this->alert('success', 'User deleted successfully.', ['position' => 'bottom-end']);
        }
    }

    public function restoreUser($userId)
    {
        $user = User::onlyTrashed()->find($userId);
        if ($user) {
            $user->restore();
            $this->alert('success', 'User restored successfully.', ['position' => 'bottom-end']);
        }
    }

    public function forceDeleteUser($userId)
    {
        $user = User::onlyTrashed()->find($userId);
        if ($user) {
            try {
                DB::beginTransaction();

                // Get all subscriptions for the user
                $subscriptions = Subscription::withoutGlobalScope(SuppressingScope::class)
                    ->where('subscriber_id', $userId)
                    ->get();

                foreach ($subscriptions as $subscription) {
                    // Delete related payments
                    Payment::where('subscription_id', $subscription->id)->delete();

                    // Delete subscription notes if you have them
                    Note::where('subscription_id', $subscription->id)->delete();

                    // Delete the subscription (regular delete since it doesn't use soft deletes)
                    $subscription->delete();
                }

                // Delete user's payments
                Payment::where('user_id', $userId)->delete();

                // Finally, force delete the user (this one uses soft delete)
                $user->forceDelete();

                DB::commit();

                $this->alert('success', 'User and all related data permanently deleted.', ['position' => 'bottom-end']);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->alert('error', 'Error deleting user: ' . $e->getMessage(), ['position' => 'bottom-end']);
            }
        }
    }

    public function toggleActive($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->active = !$user->active;
            $user->save();
            $this->alert('success', 'User status updated successfully.', ['position' => 'bottom-end']);
        }
    }

    public function impersonateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            session()->put('impersonated_by', Auth::id());
            Auth::login($user);
            return redirect()->route('dashboard');
        }
    }

    #[Computed]
    public function users()
    {
        return User::role('user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$this->search%")
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function admins()
    {
        return User::role('admin')
            ->when($this->adminSearch !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->adminSearch . '%')
                        ->orWhere('last_name', 'like', '%' . $this->adminSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->adminSearch . '%')
                        ->orWhere('username', 'like', '%' . $this->adminSearch . '%')
                        ->orWhere('company', 'like', '%' . $this->adminSearch . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function trashedUsers()
    {
        return User::onlyTrashed()
            ->when($this->trashedSearch !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->trashedSearch . '%')
                        ->orWhere('last_name', 'like', '%' . $this->trashedSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->trashedSearch . '%')
                        ->orWhere('username', 'like', '%' . $this->trashedSearch . '%')
                        ->orWhere('company', 'like', '%' . $this->trashedSearch . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.admin.user.user-management')
            ->layout('layouts.app');
    }
}
