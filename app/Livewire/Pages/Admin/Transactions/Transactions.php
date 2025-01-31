<?php

namespace App\Livewire\Pages\Admin\Transactions;

use App\Models\Payment\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;

class Transactions extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $searchAll = '';
    public $searchPending = '';
    public $searchApproved = '';
    public $searchFailed = '';
    public $searchCancelled = '';
    public $searchRefunded = '';
    public $perPage = 10;
    public $selectedTab = 'all';

    protected $queryString = [
        'searchAll' => ['except' => ''],
        'searchPending' => ['except' => ''],
        'searchApproved' => ['except' => ''],
        'searchFailed' => ['except' => ''],
        'searchCancelled' => ['except' => ''],
        'searchRefunded' => ['except' => ''],
        'selectedTab' => ['except' => 'all'],
    ];

    public function updatingSearchAll()
    {
        $this->resetPage();
    }

    public function updatingSearchPending()
    {
        $this->resetPage();
    }

    public function updatingSearchApproved()
    {
        $this->resetPage();
    }

    public function updatingSearchFailed()
    {
        $this->resetPage();
    }

    public function updatingSearchCancelled()
    {
        $this->resetPage();
    }

    public function updatingSearchRefunded()
    {
        $this->resetPage();
    }


    #[Computed]
    public function allPayments()
    {
        return $this->baseQuery()
            ->when($this->searchAll, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function pendingPayments()
    {
        return $this->baseQuery('pending')
            ->when($this->searchPending, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function approvedPayments()
    {
        return $this->baseQuery('approved')
            ->when($this->searchApproved, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function failedPayments()
    {
        return $this->baseQuery('failed')
            ->when($this->searchFailed, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function cancelledPayments()
    {
        return $this->baseQuery('cancelled')
            ->when($this->searchCancelled, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function refundedPayments()
    {
        return $this->baseQuery('refunded')
            ->when($this->searchRefunded, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    protected function baseQuery(string $status = 'all')
    {
        $query = Payment::with([
            'user' => function ($query) {
                $query->withTrashed();
            },
            'plan',
            'subscription'
        ]);


        return match($status) {
            'pending' => $query->where('status', 'pending'),
            'approved' => $query->where('status', 'approved'),
            'failed' => $query->where('status', 'failed'),
            'cancelled' => $query->where('status', 'cancelled'),
            'refunded' => $query->where('status', 'refunded'),
            default => $query
        };
    }

    protected function searchCallback()
    {
        return function ($query) {
            $searchTerm = $this->{match($this->selectedTab) {
                'pending' => 'searchPending',
                'approved' => 'searchApproved',
                'failed' => 'searchFailed',
                'cancelled' => 'searchCancelled',
                'refunded' => 'searchRefunded',
                default => 'searchAll',
            }};

            $query->where(function($q) use ($searchTerm) {
                $q->where('transaction_id', 'like', "%$searchTerm%")
                  ->orWhere('amount', 'like', "%$searchTerm%")
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->withTrashed() // Include soft deleted users in search
                          ->where(function($subQuery) use ($searchTerm) {
                              $subQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$searchTerm%")
                                      ->orWhere('email', 'like', "%$searchTerm%")
                                      ->orWhere('username', 'like', '%' . $searchTerm . '%');
                          });
                  });
            });
        };
    }

    public function impersonateUser($userId)
    {
        $user = config('auth.providers.users.model')::find($userId);
        if ($user) {
            session()->put('impersonated_by', auth()->id());
            auth()->login($user);
            return redirect()->route('dashboard');
        }
    }
    public function render()
    {
        return view('livewire.pages.admin.transactions.transactions')
            ->layout('layouts.app');
    }
}
