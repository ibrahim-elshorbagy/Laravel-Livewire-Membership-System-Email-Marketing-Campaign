<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmailList;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

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

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => 'all']
    ];

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->selectPage = false;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->selectPage = false;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
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
    }

    public function updatedSelectedEmails()
    {
        $this->selectPage = count($this->selectedEmails) === $this->emails->count();
        $this->selectAll = false;
    }

    public function selectAll()
    {
        $this->selectAll = true;
        $this->selectedEmails = $this->emailsQuery->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function deselectAll()
    {
        $this->selectAll = false;
        $this->selectPage = false;
        $this->selectedEmails = [];
    }

    public function deleteEmail($emailId)
    {
        DB::transaction(function () use ($emailId) {
            $email = EmailList::find($emailId);
            if ($email) {
                $email->delete();
                $totalEmailCount = EmailList::where('user_id', $this->user->id)
                    ->count();
                $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                $this->alert('success', 'Email deleted successfully!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
        });
    }

    public function bulkDelete()
    {
        DB::transaction(function () {
            $emailsCount = count($this->selectedEmails);
            EmailList::whereIn('id', $this->selectedEmails)->delete();

            $totalEmailCount = EmailList::where('user_id', $this->user->id)
                ->count();
            $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);

            $this->selectedEmails = [];
            $this->selectAll = false;
            $this->selectPage = false;

            $this->alert('success', "$emailsCount emails deleted successfully!", [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        });
    }

    public function getEmailsQueryProperty()
    {
        return EmailList::where('user_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where('email', 'like', '%'.$this->search.'%');
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

    // Add these methods to the component class
    public function bulkUpdateStatus($status)
    {
        $this->validate(['selectedEmails' => 'required|array']);

        EmailList::whereIn('id', $this->selectedEmails)
            ->update(['active' => $status]);

        $this->alert('success', "Status updated for ".count($this->selectedEmails)." emails!", [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);

        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->selectPage = false;
    }

    public function toggleStatus($emailId)
    {
        $email = EmailList::find($emailId);
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
