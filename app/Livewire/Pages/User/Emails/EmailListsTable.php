<?php

namespace App\Livewire\Pages\User\Emails;

use App\Jobs\ClearEmailStatus;
use App\Jobs\DeleteEmails;
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
    public $sortField = 'email';
    public $sortDirection = 'asc';
    public $selectedEmails = [];
    public $selectPage = false;
    public $selectionType = 'page';
    public $user;
    public $emailLimit;
    public $selectedEmailId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'email'],
        'sortDirection' => ['except' => 'asc']
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
            'statusFilter' => ['required', 'string', Rule::in(['all', 'FAIL', 'SENT', 'NULL'])],
            'sortField' => ['required', 'string', Rule::in(['email', 'status', 'send_time', 'sender_email'])],
            'sortDirection' => ['required', 'string', Rule::in(['asc', 'desc'])],
            'selectedEmails' => 'array',
            'selectedEmails.*' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
            'selectedEmailId' => [ // Make it nullable
                'nullable',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user->id);
                }),
            ],
            'selectionType' => ['required', 'string', Rule::in(['page', 'all'])],
        ];
    }

    protected $messages = [
        'selectedEmails.*.exists' => 'One or more selected emails are invalid.',
        'statusFilter.in' => 'Invalid status filter selected.',
        'sortField.in' => 'Invalid sort field selected.',
        'sortDirection.in' => 'Invalid sort direction selected.',
        'selectionType.in' => 'Invalid selection type.',
        'selectedEmailId.required' => 'No email selected.',
        'selectedEmailId.exists' => 'Selected email is invalid.',

    ];

    public function mount()
    {
        $this->user = auth()->user();
        if (!$this->user) {
            return redirect()->route('login');
        }

        $this->validate([
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
            'statusFilter' => ['required', 'string', Rule::in(['all', 'FAIL', 'SENT', 'NULL'])],
            'sortField' => ['required', 'string', Rule::in(['email', 'status', 'send_time', 'sender_email'])],
            'sortDirection' => ['required', 'string', Rule::in(['asc', 'desc'])],
            'selectionType' => ['required', 'string', Rule::in(['page', 'all'])],
        ]);

        $this->emailLimit = $this->checkEmailLimit();
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

    public function updatedSortField()
    {
        $this->validateOnly('sortField');
    }

    public function updatedSortDirection()
    {
        $this->validateOnly('sortDirection');
    }

    public function updatedSelectionType()
    {
        $this->validateOnly('selectionType');

        try {
            if ($this->selectionType === 'page') {
                $this->selectedEmails = $this->emails->pluck('id')->map(fn($id) => (string) $id)->toArray();
            } else {
                $this->selectedEmails = $this->emailsQuery->pluck('id')->map(fn($id) => (string) $id)->toArray();
            }
            $this->validateOnly('selectedEmails');
        } catch (\Exception $e) {
            $this->alert('error', 'Error updating selection: ' . $e->getMessage());
            $this->resetSelections();
        }
    }

    protected function resetSelections()
    {
        $this->selectedEmails = [];
        $this->selectPage = false;
    }

    public function getEmailsQueryProperty()
    {
        try {
            return EmailList::where('user_id', $this->user->id)
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('email', 'like', '%'.trim($this->search).'%')
                          ->orWhere('sender_email', 'like', '%'.trim($this->search).'%')
                          ->orWhere('status', 'like', '%'.trim($this->search).'%');
                    });
                })
                ->when($this->statusFilter !== 'all', function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->orderBy($this->sortField, $this->sortDirection);
        } catch (\Exception $e) {
            $this->alert('error', 'Error building query: ' . $e->getMessage());
            return EmailList::where('user_id', $this->user->id);
        }
    }

    public function clearAllStatus()
    {
        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to update!');
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $query = EmailList::where('user_id', $this->user->id)
                                ->whereIn('id', $this->selectedEmails);

                $affected = $query->update([
                    'status' => 'NULL',
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                if ($affected > 0) {
                    $this->alert('success', "$affected emails cleared successfully!", [
                        'position' => 'bottom-end',
                        'timer' => 3000,
                        'toast' => true,
                    ]);
                } else {
                    $this->alert('info', 'No emails were updated.');
                }
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear emails: ' . $e->getMessage());
        }

        $this->resetSelections();
    }
    public function clearSingleStatus($emailId)
    {
        $this->selectedEmailId = $emailId;

        $this->validate([
            'selectedEmailId' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user->id);
                }),
            ]
        ]);

        try {
            DB::transaction(function () {
                $email = EmailList::where('user_id', $this->user->id)
                                ->where('id', $this->selectedEmailId)
                                ->first();

                if (!$email) {
                    throw new \Exception('Email not found');
                }

                $email->update([
                    'status' => 'NULL',
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                $this->alert('success', 'Status cleared successfully!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear status: ' . $e->getMessage());
        }

        $this->selectedEmailId = null;
    }

    public function deleteEmail($emailId)
    {
        $this->selectedEmailId = $emailId;

        $this->validate([
            'selectedEmailId' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user->id);
                }),
            ]
        ]);

        try {
            DB::transaction(function () {
                $email = EmailList::where('user_id', $this->user->id)
                                ->where('id', $this->selectedEmailId)
                                ->first();

                if (!$email) {
                    throw new \Exception('Email not found');
                }

                $email->delete();
                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);


                $this->alert('success', 'Email deleted successfully!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);

                $this->emailLimit = $this->checkEmailLimit();
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete email: ' . $e->getMessage());
        }

        $this->selectedEmailId = null;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to delete!');
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $query = EmailList::where('user_id', $this->user->id)
                                ->whereIn('id', $this->selectedEmails);

                $count = $query->count();
                $query->delete();

                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);

                $this->alert('success', "$count emails deleted successfully!", [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);

                $this->resetSelections();
                $this->emailLimit = $this->checkEmailLimit();
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete emails: ' . $e->getMessage());
        }
    }
    public function clearAllFailedStatus()
    {
        try {
            $count = EmailList::where('user_id', $this->user->id)
                            ->where('status', 'FAIL')
                            ->count();

            if ($count === 0) {
                $this->alert('info', 'No failed status emails found.');
                return;
            }

            ClearEmailStatus::dispatch($this->user->id, 'FAIL', false);

            $this->alert('success', "Processing {$count} emails. This may take a while.", [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage());
        }
    }

    public function clearAllSentStatus()
    {
        try {
            $count = EmailList::where('user_id', $this->user->id)
                            ->where('status', 'SENT')
                            ->count();

            if ($count === 0) {
                $this->alert('info', 'No sent status emails found.');
                return;
            }

            ClearEmailStatus::dispatch($this->user->id, 'SENT', false);

            $this->alert('success', "Processing {$count} emails. This may take a while.", [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage());
        }
    }

    public function clearAllEmailsStatus()
    {
        try {
            $count = EmailList::where('user_id', $this->user->id)->count();

            if ($count === 0) {
                $this->alert('info', 'No emails found.');
                return;
            }

            ClearEmailStatus::dispatch($this->user->id, null, false);

            $this->alert('success', "Processing {$count} emails. This may take a while.", [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage());
        }
    }

    public function deleteAllEmails()
    {
        try {
            $count = EmailList::where('user_id', $this->user->id)->count();

            if ($count === 0) {
                $this->alert('info', 'No emails to delete.');
                return;
            }

            DeleteEmails::dispatch($this->user->id, false);


            $this->alert('success', "Processing deletion of {$count} emails. This may take a while.", [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete emails: ' . $e->getMessage());
        }
    }

    // Modify existing methods to use jobs
    public function clearStatus($status)
    {
        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to update!');
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () use ($status) {
                $query = EmailList::where('user_id', $this->user->id)
                                ->whereIn('id', $this->selectedEmails);

                if ($status) {
                    $query->where('status', $status);
                }

                $affected = $query->update([
                    'status' => 'NULL',
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                if ($affected > 0) {
                    $this->alert('success', "$affected emails updated successfully!", [
                        'position' => 'bottom-end',
                        'timer' => 3000,
                        'toast' => true,
                    ]);
                } else {
                    $this->alert('info', 'No emails were updated.');
                }
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear status: ' . $e->getMessage());
        }

        $this->resetSelections();
    }




    public function updatedSelectPage($value)
    {
        try {
            if ($value) {
                $this->selectedEmails = $this->emails->pluck('id')->map(fn($id) => (string) $id)->toArray();
            } else {
                $this->selectedEmails = [];
            }
            $this->validateOnly('selectedEmails');
        } catch (\Exception $e) {
            $this->alert('error', 'Error updating selection: ' . $e->getMessage());
            $this->resetSelections();
        }
    }



    protected function checkEmailLimit()
    {
        try {
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
            $currentEmails = EmailList::where('user_id', $this->user->id)->count();

            if ($currentEmails > $allowedEmails) {
                return [
                    'show' => true,
                    'excess' => $currentEmails - $allowedEmails,
                    'allowed' => $allowedEmails,
                    'current' => $currentEmails
                ];
            }

            return [
                'show' => false,
                'allowed' => $allowedEmails,
                'current' => $currentEmails
            ];
        } catch (\Exception $e) {
            return [
                'show' => false,
                'error' => true,
                'message' => 'Unable to check email limit'
            ];
        }
    }
    public function getTotalRecordsProperty()
    {
        try {
            return $this->emailsQuery->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getEmailsProperty()
    {
        try {
            return $this->emailsQuery->paginate($this->perPage);
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function render()
    {

            return view('livewire.pages.user.emails.email-lists-table', [
                'emails' => $this->emails,
                'totalRecords' => $this->totalRecords
            ])->layout('layouts.app', ['title' => 'Email Lists']);

    }
}
