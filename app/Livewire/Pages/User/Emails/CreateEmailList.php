<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\EmailList;
use App\Jobs\ProcessEmailFile;
use App\Models\EmailListName;
use App\Models\JobProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;

class CreateEmailList extends Component
{
    use LivewireAlert, WithFileUploads;

    public $file;
    public $emails = [];
    public $remainingQuota = 0;
    public $user;
    public $processing = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public $list_id = null;
    public $allow_duplicates = false;
    public $emailLists;


    protected $rules = [
        'list_id' => 'required|exists:email_list_names,id',
        'file' => 'nullable|file|mimes:txt,csv,xlsx,xls',
    ];

    protected $messages = [
        'list_id.required' => 'Please select a list.',
        'list_id.exists' => 'The selected list is invalid.',
        'file.mimes' => 'The file must be a txt, csv, xlsx, or xls file.',
    ];
    public function mount()
    {
        $this->user = auth()->user();
        $this->remainingQuota = $this->user->balance('Subscribers Limit');

        $this->emailLists = EmailListName::where('user_id', $this->user->id)->get();

        if ($this->remainingQuota == 0 || $this->hasActiveJobs()) {
            return redirect()->route('welcome');
        }
    }

    public function hasActiveJobs()
    {
        return DB::table('jobs')
            ->where('queue', 'high')
            ->where(function($query) {
                $query->where('payload', 'like', '%"userId":' . $this->user->id . '%')
                    ->orWhere('payload', 'like', '%"user_id":' . $this->user->id . '%')
                    ->orWhere('payload', 'like', '%i:' . $this->user->id . ';%');
            })
            ->exists();
    }
    public function processFile()
    {
        try {
            $this->validate([
                'file' => 'required|file',
                'list_id' => 'required|exists:email_list_names,id',
            ]);

            $path = $this->file->store('temp-emails');

            ProcessEmailFile::dispatch(
                $path,
                $this->user->id,
                $this->remainingQuota,
                $this->list_id,
                $this->allow_duplicates
            );


            $this->file = null;
            $this->processing = false;
            Session::flash('success', 'File uploaded successfully, Processing will begin shortly.');
            return $this->redirect(route('user.emails.index'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Error uploading file: ' . $e->getMessage(), ['position' => 'bottom-end']);
            $this->processing = false;
        }
    }

    public function saveEmails($emails)
    {
        try {
            $this->validate([
                'list_id' => 'required|exists:email_list_names,id'
            ]);

            $emailsCount = count($emails);

            if ($emailsCount === 0) {
                $this->alert('error', 'No valid emails found', ['position' => 'bottom-end']);
                return;
            }

            if (!$this->user->canConsume('Subscribers Limit', $emailsCount)) {
                $this->alert('error', 'Not enough quota remaining', ['position' => 'bottom-end']);
                return;
            }

            DB::transaction(function() use ($emails) {
                $records = collect($emails)->map(fn($email) => [
                    'user_id' => $this->user->id,
                    'list_id' => $this->list_id,
                    'email' => $email,
                    'created_at' => now(),
                    'updated_at' => now()
                ])->toArray();

                if (!$this->allow_duplicates) {
                    // Check for existing emails in this list
                    $existingEmails = EmailList::where('user_id', $this->user->id)
                        ->where('list_id', $this->list_id)
                        ->whereIn('email', $emails)
                        ->pluck('email')
                        ->toArray();

                    // Filter out existing emails
                    $records = array_filter($records, function($record) use ($existingEmails) {
                        return !in_array($record['email'], $existingEmails);
                    });
                }

                if (!empty($records)) {
                    EmailList::insert($records);
                    $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                    $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                }
            });

            Session::flash('success', 'Emails saved successfully.');
            return $this->redirect(route('user.emails.index'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'An error occurred: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.create-email-list')
            ->layout('layouts.app', ['title' => 'Add New Emails']);
    }
}
