<?php

namespace App\Livewire\Pages\User\Emails;


use App\Jobs\DeleteEmails;
use App\Models\Campaign\EmailHistory;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmailList;
use App\Models\EmailListName;
use App\Models\JobProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use LucasDotVin\Soulbscription\Models\Feature;

class EmailListsTable extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $searchName = '';
    public $perPage = 10;
    public $sortDirection = 'asc';
    public $orderBy = 'email';
    public $selectedEmails = [];
    public $selectPage = false;
    private $user;
    public $subscriberBalance;
    public $emailLimit;

    public $selectedEmailId = null;
    public $editEmail = '';
    public $editName = '';
    public $editSoftBounceCounter = '';
    public $editIsHardBounce = '';

    #[Url]
    public $selectedList = '';

    public $listName = '';
    public $editingListId = null;



    // Job Progress
    public $hasActiveJobsFlag = false;
    protected $listeners = ['jobStatusUpdated' => 'updateJobStatus'];


    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortDirection' => ['except' => 'asc']
    ];


    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'perPage' => 'required|integer|in:10,25,50,100',
            'sortDirection' => ['required', 'string', Rule::in(['asc', 'desc'])],
            'orderBy' => ['required', 'string', Rule::in(['email', 'name','soft_bounce_counter','is_hard_bounce'])],
            'selectedEmails' => 'array',
            'selectedList' => 'nullable|string|max:255',
            'selectedEmails.*' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'selectedEmailId' => [
                'required',
                Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'editingListId' => [
                'required',
                Rule::exists('editingListId', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
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
        $subscribersLimitName = Feature::find(1)?->name;
        $this->subscriberBalance = $this->user->balance($subscribersLimitName);
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

        $countProcessing = DB::table('jobs')
            ->where(function ($query) {
                $query->whereRaw("payload LIKE '%\"userId\":{$this->user->id}%'")
                    ->orWhereRaw("payload LIKE '%\"user_id\":{$this->user->id}%'")
                    ->orWhereRaw("payload LIKE '%i:{$this->user->id};%'");
            })
            ->count();

        $this->hasActiveJobsFlag = $countProcessing > 0 ? true : false ;
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




    public function updateJobStatus($status)
    {
        // Skip if no actual change
        if ($this->hasActiveJobsFlag === (bool)$status) {
            $this->skipRender();
            return;
        }

        // Force update the property
        $this->hasActiveJobsFlag = (bool)$status;

        // Add this to ensure Livewire knows to update
        $this->dispatch('refresh-action-buttons');
    }



    // -------------------------------------------------------------------------------------------------------------------------------------------------------------








    public function getEmailsQueryProperty()
    {
        if (!$this->selectedList) {
            return EmailList::where('id', 0); // Return empty query if no list selected
        }

        // Start with base query with necessary columns only
        $query = EmailList::query()
            ->select(['id', 'email','name','soft_bounce_counter','is_hard_bounce'])
            ->with(['history' => function($query) {
                $query->with(['campaign:id,message_id', 'campaign.message:id,email_subject'])
                    ->orderBy('sent_time', 'desc');
            }])
            ->where('user_id', Auth::id())
            ->whereHas('emailListName', function($query) {
                $query->where('name', $this->selectedList);
            });

        // Apply search filters only if search term exists
        if (trim($this->search)) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('email', 'like', $searchTerm);
            });
        }
        if (trim($this->searchName)) {
            $searchNameTerm = '%' . trim($this->searchName) . '%';
            $query->where(function($q) use ($searchNameTerm) {
                $q->where('name', 'like', $searchNameTerm);
            });
        }


        return $query->orderBy($this->orderBy, $this->sortDirection);
    }


    public function getHardBounceCount()
    {
        if (!$this->selectedList) {
            return 0;
        }

        // Count hard bounce emails in this list
        return EmailList::where('user_id', Auth::id())
            ->where('is_hard_bounce', true)
            ->whereHas('emailListName', function($query) {
                $query->where('name', $this->selectedList);
            })
            ->count();
    }


    public function deleteEmails($type = 'selected', $emailId = null)
    {
        // Validate method parameters using Laravel's validator
        $validator = Validator::make(
            [
                'type' => $type,
                'emailId' => $emailId
            ],
            [
                'type' => ['required', 'string', Rule::in(['selected', 'single', 'all', 'hard_bounce'])],
                'emailId' => ['nullable', 'integer', Rule::exists('email_lists', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })],
            ]
        );

        if ($validator->fails()) {
            $this->alert('error', $validator->errors()->first(), ['position' => 'bottom-end']);
            return;
        }

        $this->user = auth()->user();

        try {

            DB::transaction(function () use ($type, $emailId) {
                // Build the query based on deletion type
                $query = EmailList::where('user_id', Auth::id());

                switch ($type) {
                    case 'single':
                        // Delete single email
                        $query->where('id', $emailId);
                        break;

                    case 'selected':
                        if (empty($this->selectedEmails)) {
                            $this->alert('error', 'Please select emails to delete!', ['position' => 'bottom-end']);
                            return;
                        }
                        $query->whereIn('id', $this->selectedEmails);
                        break;

                    case 'all':
                        if (!$this->selectedList) {
                            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
                            return;
                        }
                        $query->whereHas('emailListName', function($query) {
                                    $query->where('name', $this->selectedList);
                                });
                        break;

                    case 'hard_bounce':
                        if (!$this->selectedList) {
                            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
                            return;
                        }
                        $query->where('is_hard_bounce', true)
                                ->whereHas('emailListName', function($query) {
                                    $query->where('name', $this->selectedList);
                                });
                        break;
                }

                $count = $query->count();

                if ($count === 0) {
                    $this->alert('info', 'No emails to delete.', ['position' => 'bottom-end']);
                    return;
                }

                // Use job for large deletions
                if ($count > 10000) {
                    DeleteEmails::dispatch(Auth::id(), $this->selectedList);
                    $this->alert('success',
                        "Processing deletion of {$count} emails. This may take a while.",
                        ['position' => 'bottom-end']
                    );
                } else {
                    // Direct deletion for smaller sets
                    $query->delete();

                    // Update user's quota
                    $subscribersLimitName = Feature::find(1)?->name;
                    $this->user->forceSetConsumption(
                        $subscribersLimitName,
                        EmailList::where('user_id', Auth::id())->count()
                    );

                    // Success message based on deletion type
                    $message = match($type) {
                        'single' => 'Email deleted successfully!',
                        'selected' => "{$count} selected emails deleted successfully!",
                        'all' => "All emails in list deleted successfully!",
                        'hard_bounce' => "{$count} hard bounce emails deleted successfully!",
                    };

                    $this->alert('success', $message, ['position' => 'bottom-end']);
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
    // Email History

    public $historyId;
    public $emailId;

    public function deleteHistory($historyId)
    {
        try {
            $this->historyId = $historyId;

            $this->validate([
                'historyId' => [
                    'required',
                    'integer',
                    Rule::exists('email_histories', 'id')
                ],
            ]);

            $history = EmailHistory::whereHas('email', function($query) {
                $query->where('user_id', auth()->id());
            })->findOrFail($this->historyId);

            $history->delete();

            $this->alert('success', 'History record deleted successfully!', [
                'position' => 'bottom-end',
            ]);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete history record!', [
                'position' => 'bottom-end',
            ]);
        }
    }

    public function deleteAllHistory($emailId)
    {
        try {
            $this->emailId = $emailId;
            $this->validate([
                'emailId' => [
                    'required',
                    'integer',
                    Rule::exists('email_lists', 'id')
                ],
            ]);

            $count = EmailHistory::where('email_id', $this->emailId)->count();

            if ($count === 0) {
                $this->alert('info', 'No history records to delete!', [
                    'position' => 'bottom-end',
                ]);
                return;
            }

            EmailHistory::whereHas('email', function($query) {
                $query->where('user_id', auth()->id());
            })->where('email_id', $this->emailId)->delete();

            $this->alert('success', "{$count} history records deleted successfully!", [
                'position' => 'bottom-end',
            ]);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete history records!', [
                'position' => 'bottom-end',
            ]);
        }
    }

    /**
     * Export the emails from the selected list to Excel (CSV)
     */
    public function exportToExcel()
    {
        if (!$this->selectedList) {
            $this->alert('error', 'Please select a list first!', ['position' => 'bottom-end']);
            return;
        }

        try {
            // Get the list name for the file name
            $listName = $this->selectedList;

            if (!$listName) {
                $listName = "email-list";
            }

            // Sanitize filename
            $fileName = preg_replace('/[^a-z0-9_\-]/i', '_', $listName) . '_export_' . date('Y-m-d') . '.csv';

            // Create CSV content
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            // Get emails from the selected list with required columns only
            $emails = EmailList::select('email', 'name', 'soft_bounce_counter', 'is_hard_bounce')
                ->where('user_id', Auth::id())
                ->whereHas('emailListName', function($query) {
                    $query->where('name', $this->selectedList);
                })
                ->orderBy($this->orderBy, $this->sortDirection)
                ->get();

            // Create and return the CSV
            $callback = function() use ($emails) {
                $file = fopen('php://output', 'w');

                // Add BOM for Excel to recognize UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Use semicolon as delimiter
                $delimiter = ';';
                $enclosure = '"';

                // Add headers
                fputcsv($file, ['Email', 'Name', 'Soft Bounce Counter', 'Hard Bounce'], $delimiter, $enclosure);

                // Add data
                foreach ($emails as $email) {
                    fputcsv($file, [
                        $email->email,
                        $email->name ?: '',
                        (string)$email->soft_bounce_counter, // Cast to string
                        $email->is_hard_bounce ? 'Yes' : 'No'
                    ], $delimiter, $enclosure);
                }

                fclose($file);
            };

            $this->alert('success', 'Export started!', ['position' => 'bottom-end']);
            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            $this->alert('error', 'Export failed: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }


    // -------------------------------------------------------------------------------------------------------------------------------------------------------------
    // List CRUD

    public function createList()
    {
        $this->validate([
            'listName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_list_names', 'name')
                    ->where('user_id', Auth::id())
            ],
        ]);


        try {
            EmailListName::create([
                'user_id' => Auth::id(),
                'name' => $this->listName
            ]);

            $this->listName = '';
            $this->dispatch('close-modal', 'create-list');
            $this->alert('success', 'List created successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to create list.', ['position' => 'bottom-end']);
        }
    }

    public function updateList()
    {
        // First validate the ID
        $this->validate([
            'editingListId' => [
                'required',
                'int',
                'exists:email_list_names,id'
            ]
        ]);

        // Get the validated ID
        $listId = $this->editingListId;

        // Then validate the name with the obtained ID
        $this->validate([
            'listName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_list_names', 'name')
                    ->where('user_id', Auth::id())
                    ->ignore($listId)
            ]
        ]);

        try {
            EmailListName::where('user_id', Auth::id())
                ->findOrFail($listId)
                ->update(['name' => $this->listName]);

            $this->listName = '';
            $this->dispatch('close-modal', 'edit-list-modal');
            $this->alert('success', 'List updated successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update list.', ['position' => 'bottom-end']);
        }
    }

    public function selectList($listName)
    {
        if ($this->selectedList != $listName) {
            $this->selectedList = $listName;
            $this->resetPage();
            $this->resetSelections();
            $this->dispatch('tabSelected', $listName);
        }
    }

    public function updateEmail()
    {

        $this->validate([
            'editEmail' => [
                'required',
                'email',
                Rule::unique('email_lists', 'email')
                    ->where('user_id', Auth::id())
                    ->where('list_id', $this->selectedList)
                    ->ignore($this->selectedEmailId)
            ],
            'editName' => 'nullable|string',
            'editSoftBounceCounter' => 'nullable|integer|min:0|max:255',
            'editIsHardBounce' => 'nullable|boolean'
        ]);

        try {
            $email = EmailList::where('user_id', Auth::id())
                ->findOrFail($this->selectedEmailId);

            $email->update([
                'email' => $this->editEmail,
                'name' => $this->editName,
                'soft_bounce_counter' => $this->editSoftBounceCounter,
                'is_hard_bounce' => $this->editIsHardBounce
            ]);

            $this->dispatch('close-modal', 'edit-email-modal');
            $this->alert('success', 'Email updated successfully!', ['position' => 'bottom-end']);

            // Reset form
            $this->editEmail = '';
            $this->editName = '';
            $this->editSoftBounceCounter = '';
            $this->editIsHardBounce = '';


        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update email.', ['position' => 'bottom-end']);
        }
    }

    public function deleteList($listId)
    {
        $list = EmailListName::where('user_id', Auth::id())
            ->withCount('emails')
            ->findOrFail($listId);

        if ($list->emails_count > 30000) {
            // Use job for large lists
            DeleteEmails::dispatch(Auth::id(), $listId);
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
    // Properties





    public function getEmailsProperty()
    {
        if (!$this->selectedList) {
            return collect([]);
        }

        return $this->emailsQuery->paginate($this->perPage);
    }



    public function getListsProperty()
    {
        return EmailListName::where('user_id', Auth::id())
            ->withCount('emails')
            ->get();
    }


    protected function checkEmailLimit()
    {
        $this->user = auth()->user();

        try {
            $subscription = $this->user->subscription;
            if (!$subscription || !$subscription->plan) {
                return ['show' => false];
            }

            $subscribersLimitName = Feature::find(1)?->name;

            $emailFeature = $subscription->plan->features()
                ->where('name', $subscribersLimitName)
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
                'selectedListName' => $selectedListName,
                'hasActiveJobsFlag'  => $this->hasActiveJobsFlag,
                'subscriberBalance' => $this->subscriberBalance,

            ])->layout('layouts.app', ['title' => 'Email Lists']);

    }
}
