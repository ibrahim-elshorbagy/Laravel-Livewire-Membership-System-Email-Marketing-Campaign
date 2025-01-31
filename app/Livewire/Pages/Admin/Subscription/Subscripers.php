<?php

namespace App\Livewire\Pages\Admin\Subscription;

use App\Models\Payment\Payment;
use App\Models\Subscription\Note;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

class Subscripers extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $searchAll = '';
    public $searchCanceled = '';
    public $searchSuppressed = '';
    public $searchExpired = '';
    public $perPage = 10;
    public $selectedTab = 'all';

    protected $queryString = [
        'searchAll' => ['except' => ''],
        'searchCanceled' => ['except' => ''],
        'searchSuppressed' => ['except' => ''],
        'searchExpired' => ['except' => ''],
        'selectedTab' => ['except' => 'all'],
    ];

    public function updatingSearchAll()
    {
        $this->resetPage();
    }

    public function updatingSearchCanceled()
    {
        $this->resetPage();
    }

    public function updatingSearchSuppressed()
    {
        $this->resetPage();
    }

    public function updatingSearchExpired()
    {
        $this->resetPage();
    }



    #[Computed]
    public function allSubscriptions()
    {
        return $this->baseQuery()
            ->when($this->searchAll, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function canceledSubscriptions()
    {
        return $this->baseQuery('canceled')
            ->when($this->searchCanceled, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function suppressedSubscriptions()
    {
        return $this->baseQuery('suppressed')
            ->when($this->searchSuppressed, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function expiredSubscriptions()
    {
        return $this->baseQuery('expired')
            ->where('expired_at', '<', now())
            ->when($this->searchExpired, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    protected function baseQuery(string $status = 'all')
    {
        $query = Subscription::with(['plan', 'subscriber' => function ($query) {
            $query->withTrashed();
        }])
        ->withoutGlobalScope(SuppressingScope::class);

        return match($status) {
            'canceled' => $query->whereNotNull('canceled_at'),
            'suppressed' => $query->whereNotNull('suppressed_at'),
            'expired' => $query->whereNotNull('expired_at'),
            default => $query
        };
    }

    protected function searchCallback()
    {
        return function ($query) {
            $searchTerm = $this->{match($this->selectedTab) {
                'canceled' => 'searchCanceled',
                'suppressed' => 'searchSuppressed',
                'expired' => 'searchExpired',
                default => 'searchAll',
            }};

            $query->whereHas('subscriber', function ($q) use ($searchTerm) {
                $q->withTrashed()
                    ->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$searchTerm%")
                        ->orWhere('first_name', 'like', "%$searchTerm%")
                        ->orWhere('last_name', 'like', "%$searchTerm%")
                        ->orWhere('email', 'like', "%$searchTerm%")
                        ->orWhere('username', 'like', "%$searchTerm%");
                });
            });
        };
    }
    public function getSubscriptionPayment($subscriptionId)
    {
        return Payment::where('subscription_id', $subscriptionId)
            ->latest()
            ->first();
    }

    public function getSubscriptionNote($subscriptionId)
    {
        return Note::where('subscription_id', $subscriptionId)
            ->first();
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
        return view('livewire.pages.admin.subscription.subscripers')
            ->layout('layouts.app',['title' => 'Subscriptions']);
    }
}
