<?php

namespace App\Livewire\Pages\Admin\Subscription;

use App\Models\Payment\Payment;
use App\Models\Subscription\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use App\Models\Subscription\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;

class Subscripers extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $searchAll = '';
    public $searchActive = '';
    public $searchCanceled = '';
    public $searchDeleted = '';
    public $searchSuppressed = '';
    public $searchExpired = '';
    public $searchGraceEnded = '';
    public $perPage = 10;
    public $selectedTab = 'all';
    public $selectedSubscriptionId = null;
    public $noteContent = '';

    protected $queryString = [
        'searchAll' => ['except' => ''],
        'searchActive' => ['except' => ''],
        'searchCanceled' => ['except' => ''],
        'searchSuppressed' => ['except' => ''],
        'searchExpired' => ['except' => ''],
        'searchGraceEnded' => ['except' => ''],
        'selectedTab' => ['except' => 'all'],
    ];

    public function updatingSearchAll()
    {
        $this->resetPage();
    }

    public function updatingSearchActive()
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

    public function updatingSearchGraceEnded()
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
    public function activeSubscriptions()
    {
        return $this->baseQuery('active')
            ->whereNull('canceled_at')
            ->whereNull('suppressed_at')
            ->where(function($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->when($this->searchActive, $this->searchCallback())
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
    public function deletedSubscriptions()
    {
        return $this->baseQuery('deleted')
            ->when($this->searchDeleted, $this->searchCallback())
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

    #[Computed]
    public function graceEndedSubscriptions()
    {
        // Use the same query approach that works in the console task
        return Subscription::withoutGlobalScopes()
            ->whereNotNull('grace_days_ended_at')
            ->where('grace_days_ended_at', '<', now())
            ->with([
                'plan.features' => function($query) {
                    $query->withPivot('charges');
                },
                'subscriber' => function($query) {
                    $query->withTrashed()
                        ->with(['featureConsumptions' => function($q) {
                            $q->with(['feature']);
                        }]);
                },
                'payments' => function($query) {
                    $query->latest();
                },
                'note'
            ])
            ->when(!in_array('graceEnded', ['all', 'deleted']), function($query) {
                $query->whereHas('subscriber', function($q) {
                    $q->whereNull('deleted_at');
                });
            })
            ->when($this->searchGraceEnded, $this->searchCallback())
            ->latest()
            ->paginate($this->perPage);
    }

    protected function baseQuery(string $status = 'all')
    {
        $query = Subscription::with([
                'plan.features' => function($query) {
                    $query->withPivot('charges');
                },
                'subscriber' => function($query) {
                    $query->withTrashed()
                        ->with(['featureConsumptions' => function($q) {
                            $q->with(['feature']);
                        }]);
                },
                'payments' => function($query) {
                    $query->latest();
                },
                'note'
            ])->withoutGlobalScopes([SuppressingScope::class, StartingScope::class]);

        // Only show subscriptions with non-deleted users unless in 'all' or 'deleted' tabs
        if (!in_array($status, ['all', 'deleted'])) {
            $query->whereHas('subscriber', function($q) {
                $q->whereNull('deleted_at');
            });
        }

        return match($status) {
            'canceled' => $query->whereNotNull('canceled_at'),
            'deleted' => $query->whereHas('subscriber', function($q) {
                $q->onlyTrashed();
            }),
            'suppressed' => $query->whereNotNull('suppressed_at'),
            'expired' => $query->whereNotNull('expired_at')->where('expired_at', '<', now()),
            'graceEnded' => $query->whereNotNull('grace_days_ended_at')
                                ->where('grace_days_ended_at', '<', now()),
            'active' => $query->whereNull('canceled_at')
                            ->whereNull('suppressed_at')
                            ->where(function($q) {
                                $q->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                            }),
            default => $query
        };
    }

    protected function searchCallback()
    {
        return function ($query) {
            $searchTerm = $this->{match($this->selectedTab) {
                'canceled' => 'searchCanceled',
                'deleted' => 'searchDeleted',
                'suppressed' => 'searchSuppressed',
                'expired' => 'searchExpired',
                'graceEnded' => 'searchGraceEnded',
                'active' => 'searchActive',
                default => 'searchAll',
            }};

            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('subscriber', function ($userQuery) use ($searchTerm) {
                    $userQuery->withTrashed()
                        ->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$searchTerm%")
                                ->orWhere('email', 'like', "%$searchTerm%")
                                ->orWhere('username', 'like', "%$searchTerm%");
                        });
                })
                ->orWhereHas('plan', function($planQuery) use ($searchTerm) {
                    $planQuery->where('name', 'like', "%$searchTerm%");
                });
            });
        };
    }


    public function impersonateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            session()->put('impersonated_by', auth()->id());
            auth()->login($user);
            return redirect()->route('dashboard');
        }
    }


    public function updateNote()
    {
        $note = Note::firstOrCreate(
            ['subscription_id' => $this->selectedSubscriptionId],
            ['content' => '']
        );

        $note->update([
            'content' => $this->noteContent
        ]);

        $this->alert('success', 'Note updated successfully', ['position' => 'bottom-end']);
        $this->dispatch('close-modal','subscription-note-modal');
    }

    public function render()
    {
        return view('livewire.pages.admin.subscription.subscripers')
            ->layout('layouts.app',['title' => 'Subscriptions']);
    }
}
