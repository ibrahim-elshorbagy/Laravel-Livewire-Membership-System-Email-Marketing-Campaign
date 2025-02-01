<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmailList;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;

class EmailListsTable extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $perPage = 10;
    public $statusFilter = 'all';
    public $selectedEmails = [];
    public $selectAll = false;
    public $selectPage = false;
    public $user;
    public $emailLimit;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => 'all']
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
            'statusFilter' => 'required|string|in:all,active,inactive',
            'selectedEmails' => 'array',
            'selectedEmails.*' => 'exists:email_lists,id'
        ];
    }

    public function mount()
    {
        $this->user = auth()->user();
        if (!$this->user) {
            return redirect()->route('login');
        }
        $this->emailLimit = $this->checkEmailLimit();
    }

    public function checkEmailLimit()
    {
        $subscription = $this->user->subscription;
        if (!$subscription || !$subscription->plan) {
            return ['show' => false];
        }

        $emailFeature = $subscription->plan->features()
            ->where('name', 'Subscribers Limit')
            ->first();

        if (!$emailFeature) {
            return ['show' => false];
        }

        $allowedEmails = (int)$emailFeature->pivot->charges;
        $currentEmails = $this->emailsCount;

        if ($currentEmails > $allowedEmails) {
            return [
                'show' => true,
                'excess' => $currentEmails - $allowedEmails,
                'allowed' => $allowedEmails
            ];
        }

        return ['show' => false];
    }

    public function updatingSearch()
    {
        $this->validateOnly('search');
        $this->resetPage();
        $this->resetSelections();
    }

    public function updatedPerPage()
    {
        $this->validateOnly('perPage');
        $this->resetPage();
        $this->resetSelections();
    }

    public function updatedStatusFilter()
    {
        $this->validateOnly('statusFilter');
        $this->resetPage();
        $this->resetSelections();
    }

    protected function resetSelections()
    {
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->selectPage = false;
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedEmails = $this->emails->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedEmails = [];
        }
        $this->validateOnly('selectedEmails');
    }

    public function updatedSelectedEmails()
    {
        $this->validateOnly('selectedEmails');
        $this->selectPage = count($this->selectedEmails) === $this->emails->count();
        $this->selectAll = false;
    }

    public function selectAll()
    {
        $this->selectAll = true;
        $this->selectedEmails = $this->emailsQuery->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->validateOnly('selectedEmails');
    }

    public function deselectAll()
    {
        $this->resetSelections();
    }

    public function deleteEmail($emailId)
    {
        $email = EmailList::find($emailId);
        if (!$email || $email->user_id !== $this->user->id) {
            $this->alert('error', 'Unauthorized action!');
            return;
        }

        DB::transaction(function () use ($email) {
            try {
                $email->delete();
                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();

                // Update using the correct column names for polymorphic relationship
                DB::table('feature_consumptions')
                    ->where('subscriber_type', get_class($this->user))
                    ->where('subscriber_id', $this->user->id)
                    ->where('feature_id', function ($query) {
                        $query->select('id')
                            ->from('features')
                            ->where('name', 'Subscribers Limit');
                    })
                    ->update(['consumption' => $totalEmailCount]);

                $this->alert('success', 'Email deleted successfully!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);

                $this->emailLimit = $this->checkEmailLimit();
            } catch (\Exception $e) {
                $this->alert('error', 'Failed to delete email: ' . $e->getMessage());
            }
        });
    }

    public function bulkDelete()
    {
        $this->validate();

        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to delete!');
            return;
        }

        DB::transaction(function () {
            try {
                $emailsCount = count($this->selectedEmails);
                EmailList::where('user_id', $this->user->id)
                        ->whereIn('id', $this->selectedEmails)
                        ->delete();

                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();

                // Update using the correct column names for polymorphic relationship
                DB::table('feature_consumptions')
                    ->where('subscriber_type', get_class($this->user))
                    ->where('subscriber_id', $this->user->id)
                    ->where('feature_id', function ($query) {
                        $query->select('id')
                            ->from('features')
                            ->where('name', 'Subscribers Limit');
                    })
                    ->update(['consumption' => $totalEmailCount]);

                $this->resetSelections();
                $this->alert('success', "$emailsCount emails deleted successfully!", [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);

                $this->emailLimit = $this->checkEmailLimit();
            } catch (\Exception $e) {
                $this->alert('error', 'Failed to delete emails: ' . $e->getMessage());
            }
        });
    }

    public function getEmailsQueryProperty()
    {
        return EmailList::where('user_id', $this->user->id)
            ->when($this->search, function ($query) {
                $query->where('email', 'like', '%'.trim($this->search).'%');
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('active', $this->statusFilter === 'active');
            });
    }

    public function getEmailsCountProperty()
    {
        return $this->emailsQuery->count();
    }

    public function getEmailsProperty()
    {
        return $this->emailsQuery->paginate($this->perPage);
    }

    public function bulkUpdateStatus($status)
    {
        $this->validate();

        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to update!');
            return;
        }

        EmailList::where('user_id', $this->user->id)
                ->whereIn('id', $this->selectedEmails)
                ->update(['active' => $status]);

        $this->alert('success', "Status updated for ".count($this->selectedEmails)." emails!", [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);

        $this->resetSelections();
    }

    public function toggleStatus($emailId)
    {
        $email = EmailList::find($emailId);
        if (!$email || $email->user_id !== $this->user->id) {
            $this->alert('error', 'Unauthorized action!');
            return;
        }

        $email->update(['active' => !$email->active]);
        $this->alert('success', "Status updated!", [
            'position' => 'bottom-end',
            'timer' => 2000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.pages.user.emails.email-lists-table', [
            'emails' => $this->emails,
            'totalEmails' => $this->emailsCount
        ])->layout('layouts.app', ['title' => 'Email Lists']);
    }
}
