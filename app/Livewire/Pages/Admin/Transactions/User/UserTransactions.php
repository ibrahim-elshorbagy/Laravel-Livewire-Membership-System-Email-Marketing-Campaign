<?php

namespace App\Livewire\Pages\Admin\Transactions\User;

use App\Models\User;
use App\Models\Payment\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class UserTransactions extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public User $user;
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

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function updating($name)
    {
        if (str_starts_with($name, 'search')) {
            $this->resetPage();
        }
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
        $query = Payment::with(['user', 'plan', 'subscription'])
            ->where('user_id', $this->user->id);

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
                  ->orWhere('amount', 'like', "%$searchTerm%");
            });
        };
    }

    public function render()
    {
        return view('livewire.pages.admin.transactions.user.user-transactions')
            ->layout('layouts.app',['title' => 'User Transactions']);
    }
}
