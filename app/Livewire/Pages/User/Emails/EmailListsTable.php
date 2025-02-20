<?php

namespace App\Livewire\Pages\User\Emails;

use App\Jobs\ClearEmailStatus;
use App\Jobs\DeleteEmails;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmailList;
use App\Models\EmailListName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


    public $selectedList = null;
    public $listName = '';
    public $editingListId = null;

    public $hasActiveJobsFlag = false;
    protected $listeners = ['jobStatusUpdated' => 'updateJobStatus'];


    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'email'],
        'sortDirection' => ['except' => 'asc']
    ];
        // Add these properties
    public $pendingJobs = [
        'file_processing' => 0,
        'clear_status' => 0,
        'delete_emails' => 0
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

    protected function listRules()
    {
        return [
            'listName' => [
                'required',
                'string',
                Rule::unique('email_list_names', 'name')
                    ->where('user_id', $this->user->id)
            ],
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


    public function updateJobStatus($status)
    {
        // Micro-debug to see how long it takes
        $start = microtime(true);

        // Only update & re-render if the status actually changed
        if ($this->hasActiveJobsFlag === $status) {
            $this->skipRender(); // <— prevents re-running queries
            // Log::info('No change to hasActiveJobsFlag, skipping render.');
            return;
        }

        // Otherwise, update as normal
        $this->hasActiveJobsFlag = $status;

        $elapsed = microtime(true) - $start;
        // Log::info('updateJobStatus changed flag to '.($status ? 'true' : 'false').', took '.$elapsed.' seconds');
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
            $this->alert('error', 'Error updating selection: ' . $e->getMessage(), ['position' => 'bottom-end']);
            $this->resetSelections();
        }
    }

    public function updatedSelectedList()
    {
        $this->resetPage();
        $this->resetSelections();
    }

    protected function resetSelections()
    {
        $this->selectedEmails = [];
        $this->selectPage = false;
    }

    public function getEmailsQueryProperty()
    {
        if (!$this->selectedList) {
            return EmailList::where('id', 0); // Return empty query if no list selected
        }

        // Start with base query with necessary columns only
        $query = EmailList::query()
            ->select(['id', 'email', 'status', 'send_time', 'sender_email', 'log'])
            ->where('user_id', $this->user->id)
            ->where('list_id', $this->selectedList);

        // Apply search filters only if search term exists
        if (trim($this->search)) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('email', 'like', $searchTerm)
                ->orWhere('sender_email', 'like', $searchTerm)
                ->orWhere('status', 'like', $searchTerm);
            });
        }

        // Apply status filter if not "all"
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }



    public function clearAllStatus()
    {
        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }

        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to update!', ['position' => 'bottom-end']);
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $query = EmailList::where('user_id', $this->user->id)
                                ->where('list_id', $this->selectedList)
                                ->whereIn('id', $this->selectedEmails);

                $affected = $query->update([
                    'status' => null,
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
                    $this->alert('info', 'No emails were updated.', ['position' => 'bottom-end']);
                }
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear emails: ' . $e->getMessage(), ['position' => 'bottom-end']);
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
                    'status' => null,
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
                // $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                $this->user->forceSetConsumption('Subscribers Limit', (float) $totalEmailCount);


                $this->alert('success', 'Email deleted successfully!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                $this->emailLimit = $this->checkEmailLimit();
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete email: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }

        $this->selectedEmailId = null;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to delete!', ['position' => 'bottom-end']);
            return;
        }

        $this->validate([
            'selectedEmails' => 'array',
            'selectedEmails.*' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
        ]);

        try {
            DB::transaction(function () {
                $query = EmailList::where('user_id', $this->user->id)
                                ->whereIn('id', $this->selectedEmails);


                $count = $query->count();

                $query->delete();

                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                // $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                $this->user->forceSetConsumption('Subscribers Limit', (float) $totalEmailCount);

                $this->alert('success', "$count emails deleted successfully!", [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);

                $this->resetSelections();
                $this->emailLimit = $this->checkEmailLimit();
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete emails: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }
    public function clearAllFailedStatus()
    {

        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }
        try {
            $query = EmailList::where('user_id', $this->user->id)
                            ->where('list_id', $this->selectedList)
                            ->where('status', 'FAIL');


            $count = $query->count();

            if ($count === 0) {
                $this->alert('info', 'No failed status emails found.', ['position' => 'bottom-end']);
                return;
            }

            if ($count < 10000) {
                $query->update([
                    'status' => null,
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                $this->alert('success', "Cleared {$count} failed status emails successfully.", ['position' => 'bottom-end']);
            } else {
                ClearEmailStatus::dispatch($this->user->id, 'FAIL', false, $this->selectedList);
                $this->alert('success', "Processing clearing of {$count} failed status emails. This may take a while.");
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function clearAllSentStatus()
    {

        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }

        try {
            $query = EmailList::where('user_id', $this->user->id)
                            ->where('list_id', $this->selectedList)
                            ->where('status', 'SENT');



            $count = $query->count();

            if ($count === 0) {
                $this->alert('info', 'No sent status emails found.');
                return;
            }

            if ($count < 10000) {
                $query->update([
                    'status' => null,
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                $this->alert('success', "Cleared {$count} sent status emails successfully.");
            } else {
                ClearEmailStatus::dispatch($this->user->id, 'SENT', false, $this->selectedList);
                $this->alert('success', "Processing clearing of {$count} sent status emails. This may take a while.");
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage());
        }
    }

    public function clearAllEmailsStatus()
    {
        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }
        try {
            $query = EmailList::where('user_id', $this->user->id)
                            ->where(function ($query) {
                                $query->whereIn('status', ['SENT', 'FAIL'])
                                    ->orWhere('sender_email', '!=', null)
                                    ->orWhere('log', '!=', null);
                            })->where('list_id', $this->selectedList);


            $count = $query->count();

            if ($count === 0) {
                $this->alert('info', 'No emails found.', ['position' => 'bottom-end']);
                return;
            }

            if ($count < 10000) {
                $query->update([
                    'status' => null,
                    'send_time' => null,
                    'sender_email' => null,
                    'log' => null
                ]);

                $this->alert('success', "Cleared status for {$count} emails successfully.", ['position' => 'bottom-end']);
            } else {
                ClearEmailStatus::dispatch($this->user->id, null, false, $this->selectedList);
                $this->alert('success', "Processing clearing of status for {$count} emails. This may take a while.", ['position' => 'bottom-end']);
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear statuses: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function deleteAllEmails()
    {

        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }

        try {
            $query = EmailList::where('user_id', $this->user->id)->where('list_id', $this->selectedList);



            $count = $query->count();

            if ($count === 0) {
                $this->alert('info', 'No emails to delete.', ['position' => 'bottom-end']);
                return;
            }

            if ($count < 10000) {
                $query->delete();

                $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                // $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                $this->user->forceSetConsumption('Subscribers Limit', (float) $totalEmailCount);

                $this->alert('success', "Deleted {$count} emails successfully.", ['position' => 'bottom-end']);
            } else {
                DeleteEmails::dispatch($this->user->id, $this->selectedList);
                $this->alert('success', "Processing deletion of {$count} emails. This may take a while.", ['position' => 'bottom-end']);
            }

            $this->emailLimit = $this->checkEmailLimit();
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete emails: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    // Modify existing methods to use jobs
    public function clearStatus($status)
    {
        if (empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to update!', ['position' => 'bottom-end']);
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
                    'status' => null,
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
                    $this->alert('info', 'No emails were updated.', ['position' => 'bottom-end']);
                }
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to clear status: ' . $e->getMessage(), ['position' => 'bottom-end']);
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
            $this->alert('error', 'Error updating selection: ' . $e->getMessage(), ['position' => 'bottom-end']);
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
            $currentEmails = $this->totalEmails;

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


    public function createList()
    {
        $this->validate($this->listRules());


        try {
            EmailListName::create([
                'user_id' => $this->user->id,
                'name' => $this->listName
            ]);

            $this->listName = '';
            $this->dispatch('close-modal', 'create-list');
            $this->alert('success', 'List created successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to create list.', ['position' => 'bottom-end']);
        }
    }

    public function updateList($listId)
    {
        $this->validate([
            'listName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_list_names', 'name')
                    ->where('user_id', $this->user->id)
                    ->ignore($listId)
            ]
        ]);

        try {
            EmailListName::where('user_id', $this->user->id)
                ->findOrFail($listId)
                ->update(['name' => $this->listName]);

            $this->listName = '';
            $this->dispatch('close-modal', 'edit-list-'.$listId);
            $this->alert('success', 'List updated successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update list.', ['position' => 'bottom-end']);
        }
    }

    public function selectList($listId)
    {
        if ($this->selectedList !== $listId) {
            $this->selectedList = $listId;
            $this->resetPage();
            $this->resetSelections();
            $this->dispatch('tabSelected', $listId);
        }
    }
    public function deleteList($listId)
    {
        $list = EmailListName::where('user_id', $this->user->id)
            ->withCount('emails')
            ->findOrFail($listId);

        if ($list->emails_count > 30000) {
            // Use job for large lists
            DeleteEmails::dispatch($this->user->id, $listId);
            $this->alert('success', "Processing deletion of {$list->emails_count} emails. This may take a while.");
        } else {
            $list->delete();
            $this->alert('success', 'List deleted successfully!');
        }

        if ($this->selectedList === $listId) {
            $this->selectedList = null;
        }
    }



    public function getTotalRecordsProperty()
    {
        try {
            if (!$this->selectedList) {
                return 0;
            }
            return $this->emailsQuery->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getTotalEmailsProperty()
    {
        return EmailList::where('user_id', $this->user->id)->count();
    }

    public function getEmailsProperty()
    {
        if (!$this->selectedList) {
            return collect([]);
        }

        return $this->emailsQuery->paginate($this->perPage);
    }








    public function getListsProperty()
    {
        return EmailListName::where('user_id', $this->user->id)
            ->withCount('emails')
            ->get();
    }

    public function getTotalEmailsCountProperty()
    {
        return [
            'total' => EmailList::where('user_id', $this->user->id)->count(),
            'current_list' => $this->selectedList ? $this->totalRecords : null
        ];
    }

    public function render()
    {
            $selectedListName = null;
            if ($this->selectedList) {
                $selectedListName = EmailListName::find($this->selectedList)?->name;
            }

            return view('livewire.pages.user.emails.email-lists-table', [

                'emails' => $this->emails,
                'totalRecords' => $this->totalRecords,
                'pendingJobs' => $this->pendingJobs,
                'selectedListName' => $selectedListName,
                'emailsCount' => $this->totalEmailsCount,
                'hasActiveJobsFlag'  => $this->hasActiveJobsFlag

            ])->layout('layouts.app', ['title' => 'Email Lists']);

    }
}
