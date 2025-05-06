<?php

namespace App\Livewire\Pages\User\Report\Email;

use App\Models\User\Reports\EmailBounce;
use App\Models\UserBouncesInfo;
use App\Services\BounceMailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

class EmailBounceReport extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $type = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $selectedBounces = [];
    public $selectPage = false;


    public $selectedEmailId = null;
    public $edit_email= '';
    public $edit_type= '';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|in:soft,hard',
            'sortField' => 'required|in:email,type,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectPage' => 'boolean',
            'selectedBounces' => 'array',
            'selectedBounces.*' => 'integer|exists:email_bounces,id',

        ];
    }


    public function updatedSelectPage($value)
    {

        if ($value) {
            $this->selectedBounces = $this->bounces->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedBounces = [];
        }
    }

    public function deleteEmail($bouncesId)
    {
        $validator = Validator::make(
            [
                'bouncesId' => $bouncesId,
            ],
            [
                'bouncesId' => ['required', 'integer'],
            ]
        );

        if ($validator->fails()) {
            $this->alert('error', $validator->errors()->first(), ['position' => 'bottom-end']);
            return;
        }

        try {
            EmailBounce::Where('user_id',Auth::id())->findOrFail($bouncesId)->delete();
            $this->alert('success', 'Email deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete Email: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedBounces' => 'required|array|min:1',
            'selectedBounces.*' => 'integer|exists:email_bounces,id'
        ]);

        try {
            // Get all selected emails
            $emails = EmailBounce::Where('user_id',Auth::id())->whereIn('id', $this->selectedBounces)->get();

            foreach($emails as $email) {
                $email->delete();
            }

            $this->selectedBounces = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected Emails deleted successfully!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete Emails: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function saveEmail()
    {
        $this->validate([
            'selectedEmailId' => 'required|exists:email_bounces,id',
            'edit_email' => 'required|string|email',
            'edit_type' => 'required|string|in:soft,hard',

        ]);

        $server = EmailBounce::Where('user_id',Auth::id())->findOrFail($this->selectedEmailId);
        $server->update([
            'email' => $this->edit_email,
            'type' => $this->edit_type

        ]);

        $this->reset(['selectedEmailId', 'edit_email']);

        $this->alert('success', 'Email saved successfully!', ['position' => 'bottom-end']);
        $this->dispatch('close-modal', 'edit-email-modal');
    }

    #[On('refresh-bounce-list')]
    public function refreshBounceList()
    {
        $this->resetPage();
    }

    public function getBouncesProperty()
    {
        return EmailBounce::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function applyToEmailList()
    {
        try {

            // Get user bounce info
            $bounceService = new BounceMailService();

            $stats = $bounceService->applyBouncesToEmailList(Auth::id());

            $this->alert('success', 'Bounces applied to your email list successfully: ' ."<br>".
                $stats['hard_bounces'] . ' hard bounces and ' ."<br>".
                $stats['soft_bounces'] . ' soft bounces processed. ' ."<br>".
                $stats['converted_to_hard'] . ' soft bounces converted to hard bounces.', [
                'position' => 'center',
                'timer' => 8000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'An error occurred: ' . $e->getMessage(), [
                'position' => 'center',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }

    public function DeleteAllEmails()
    {
        try {
            $count = EmailBounce::where('user_id', Auth::id())->delete();

            $this->alert('success', "$count bounced emails deleted successfully!", [
                'position' => 'bottom-end',
                'timer' => 4000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Error deleting emails: ' . $e->getMessage(), [
                'position' => 'bottom-end',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }



    public function render()
    {
        return view('livewire.pages.user.report.email.email-bounce-report', [
            'bounces' => $this->bounces
        ])->layout('layouts.app', ['title' => 'Email Bounce Report']);
    }
}
