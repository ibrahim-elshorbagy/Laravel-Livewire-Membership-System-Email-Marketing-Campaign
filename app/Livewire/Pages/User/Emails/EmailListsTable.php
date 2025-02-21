<?php

namespace App\Livewire\Pages\User\Emails;


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
    public $sortDirection = 'asc';
    public $selectedEmails = [];
    public $selectPage = false;
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
        'sortDirection' => ['except' => 'asc']
    ];
        // Add these properties
    public $pendingJobs = [
        'file_processing' => 0,
        'delete_emails' => 0
    ];


    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
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
        'sortDirection.in' => 'Invalid sort direction selected.',
        'selectedEmailId.required' => 'No email selected.',
        'selectedEmailId.exists' => 'Selected email is invalid.',

    ];


    // -------------------------------------------------------------------------------------------------------------------------------------------------------------





    

    public function mount()
    {
        $this->user = auth()->user();
        if (!$this->user) {
            return redirect()->route('login');
        }

        $this->validate([
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
            'sortDirection' => ['required', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $this->emailLimit = $this->checkEmailLimit();
        session(['cached_user' => $this->user]);

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



    public function updatedSortDirection()
    {
        $this->validateOnly('sortDirection');

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








    // -------------------------------------------------------------------------------------------------------------------------------------------------------------








    public function getEmailsQueryProperty()
    {
        if (!$this->selectedList) {
            return EmailList::where('id', 0); // Return empty query if no list selected
        }

        // Start with base query with necessary columns only
        $query = EmailList::query()
            ->select(['id', 'email'])
            ->where('user_id', $this->user->id)
            ->where('list_id', $this->selectedList);

        // Apply search filters only if search term exists
        if (trim($this->search)) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('email', 'like', $searchTerm);
            });
        }


        return $query->orderBy('email', $this->sortDirection);
    }




    public function deleteEmails($type = 'selected')
    {
        // Validation checks
        if ($type === 'selected' && empty($this->selectedEmails)) {
            $this->alert('error', 'Please select emails to delete!', ['position' => 'bottom-end']);
            return;
        }

        if ($type === 'all' && !$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }

        try {
            DB::transaction(function () use ($type) {
                // Build the query based on deletion type
                $query = EmailList::where('user_id', $this->user->id);

                if ($type === 'selected') {
                    // Delete only selected emails
                    $query->whereIn('id', $this->selectedEmails);
                } else {
                    // Delete all emails in current list
                    $query->where('list_id', $this->selectedList);
                }

                $count = $query->count();

                if ($count === 0) {
                    $this->alert('info', 'No emails to delete.', ['position' => 'bottom-end']);
                    return;
                }

                // Use job for large deletions
                if ($count > 10000) {
                    DeleteEmails::dispatch($this->user->id, $this->selectedList);
                    $this->alert('success',
                        "Processing deletion of {$count} emails. This may take a while.",
                        ['position' => 'bottom-end']
                    );
                } else {
                    // Direct deletion for smaller sets
                    $query->delete();

                    // Update user's quota
                    $this->user->forceSetConsumption(
                        'Subscribers Limit',
                        EmailList::where('user_id', $this->user->id)->count()
                    );

                    $this->alert('success',
                        $type === 'selected'
                            ? "{$count} selected emails deleted successfully!"
                            : "All emails in list deleted successfully!",
                        ['position' => 'bottom-end']
                    );
                }

                // Cleanup
                $this->resetSelections();
                $this->emailLimit = $this->checkEmailLimit();
            });
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete emails: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }








    // -------------------------------------------------------------------------------------------------------------------------------------------------------------
    // List CRUD

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







    // -------------------------------------------------------------------------------------------------------------------------------------------------------------






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
            $currentEmails = $this->lists->sum('emails_count');

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

    // -------------------------------------------------------------------------------------------------------------------------------------------------------------

    public function render()
    {
            $selectedListName = null;
            if ($this->selectedList) {
                $selectedListName = EmailListName::find($this->selectedList)?->name;
            }

            return view('livewire.pages.user.emails.email-lists-table', [

                'emails' => $this->emails,
                'pendingJobs' => $this->pendingJobs,
                'selectedListName' => $selectedListName,
                'hasActiveJobsFlag'  => $this->hasActiveJobsFlag,

            ])->layout('layouts.app', ['title' => 'Email Lists']);

    }
}
