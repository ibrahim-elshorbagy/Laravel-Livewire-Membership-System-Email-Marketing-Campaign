<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\EmailList;
use App\Jobs\ProcessEmailFile;
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

    public function mount()
    {
        $this->user = auth()->user();
        $this->remainingQuota = $this->user->balance('Subscribers Limit');

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
            ]);

            $path = $this->file->store('temp-emails');

            ProcessEmailFile::dispatch($path, $this->user->id, $this->remainingQuota);


            $this->file = null;
            $this->processing = false;
            Session::flash('success', 'File uploaded successfully, Processing will begin shortly.');
            return $this->redirect(route('user.emails.index'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Error uploading file: ' . $e->getMessage());
            $this->processing = false;
        }
    }

    public function saveEmails($emails)
    {
        try {
            $emailsCount = count($emails);

            if ($emailsCount === 0) {
                $this->alert('error', 'No valid emails found');
                return;
            }

            if (!$this->user->canConsume('Subscribers Limit', $emailsCount)) {
                $this->alert('error', 'Not enough quota remaining');
                return;
            }

            DB::transaction(function() use ($emails) {
                $existingEmails = EmailList::whereIn('email', $emails)
                    ->pluck('email')
                    ->toArray();

                $newEmails = array_diff($emails, $existingEmails);

                if (!empty($newEmails)) {
                    EmailList::insert(
                        collect($newEmails)->map(fn($email) => [
                            'user_id' => $this->user->id,
                            'email' => $email,
                            'created_at' => now(),
                            'updated_at' => now()
                        ])->toArray()
                    );

                    $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                    $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                }
            });

            Session::flash('success', 'Emails saved successfully.');

            return $this->redirect(route('user.emails.index'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.user.emails.create-email-list')
            ->layout('layouts.app', ['title' => 'Add New Emails']);
    }
}
