<?php

namespace App\Livewire\Pages\User\Report\Email;

use App\Models\User\Reports\EmailBounce;
use App\Models\UserBouncesInfo;
use App\Services\BounceMailService;
use Illuminate\Support\Facades\Auth;
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
        ];
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
