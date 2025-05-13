<?php

namespace App\Livewire\Pages\Admin\SiteSettings;

use App\Models\Admin\Site\ApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Url;

class ApiRequests extends Component
{
    use WithPagination, LivewireAlert;

    #[Url]
    public $search = '';
    #[Url]
    public $userSearch = '';

    public $sortField = 'request_time';
    public $sortDirection = 'desc';
    public $status = 'all';
    public $perPage = 10;
    public $selectedRequests = [];
    public $selectPage = false;


    protected function rules()
    {
        return [
            'search' => 'nullable|string',
            'userSearch' => 'nullable|string',
            'sortField' => 'required|in:serverid,request_time,execution_time,status,error_number',
            'status' => 'required|in:failed,success,all',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectedRequests' => 'array',
            'selectedRequests.*' => 'integer|exists:api_requests,id',
            'selectPage' => 'boolean',
            'selectedErrorNumber' => 'nullable|integer|min:1|max:10',
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
        ];
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedRequests = $this->requests->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedRequests = [];
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedRequests' => 'required|array|min:1',
            'selectedRequests.*' => 'integer|exists:api_requests,id'
        ]);

        try {
            ApiRequest::whereIn('id', $this->selectedRequests)->delete();
            $this->selectedRequests = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected requests deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete requests: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function impersonateUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            session()->put('impersonated_by', auth()->id());
            auth()->login($user);
            return redirect()->route('dashboard');
        }
    }
    public function getRequestsProperty()
    {
        return ApiRequest::with(['server' => function($query) {
                $query->with(['assignedUser' => function($q) {
                    $q->select('id', 'first_name', 'last_name', 'username');
                }]);
            }])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('serverid', 'like', '%' . $this->search . '%')
                      ->orWhereRaw("JSON_EXTRACT(error_data, '$.error') LIKE ?", ['%' . $this->search . '%'])
                      ->orWhereRaw("JSON_EXTRACT(error_data, '$.message') LIKE ?", ['%' . $this->search . '%']);
                });
            })
            ->when($this->userSearch, function ($query) {
                $query->whereHas('server.assignedUser', function($userQuery) {
                    $userQuery->where(function($q) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->userSearch . '%')
                          ->orWhere('username', 'like', '%' . $this->userSearch . '%');
                    });
                });
            })->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->sortField === 'error_number', function ($query) {
                $query->orderByRaw("CAST(JSON_EXTRACT(error_data, '$.error_number') AS SIGNED) {$this->sortDirection}");
            })

            ->when($this->sortField !== 'error_number', function ($query) {
                $query->orderBy($this->sortField, $this->sortDirection);
            })
            ->paginate($this->perPage);
    }

    public function deleteAll()
    {
        try {
            ApiRequest::truncate();
            $this->selectedRequests = [];
            $this->selectPage = false;
            $this->alert('success', 'All API requests deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete requests: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public $errorData = [
        1 => 'Validation failed: Invalid data provided.',
        2 => 'Access Denied: Invalid User-Agent.',
        3 => 'Maintenance Mode: The system is under maintenance.',
        4 => 'Authentication failed: Invalid API credentials.',
        5 => 'Account inactive: User account is currently inactive.',
        6 => 'No subscription: Active subscription required.',
        7 => 'Quota exceeded: Email sending limit reached.',
        8 => 'No active campaign: No active campaign found for this server.',
        9 => 'No Emails available: No emails found for this server\'s campaign.',
        10 => 'Invalid user: No Assigned user found.',
    ];

    public $selectedErrorNumber = null;
    public $dateFrom = null;
    public $dateTo = null;


    public function bulkDeleteFiltered()
    {
        $query = ApiRequest::query();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->selectedErrorNumber) {
            $query->whereJsonContains('error_data->error_number', (int)$this->selectedErrorNumber);
        }

        if ($this->dateFrom) {
            $query->where('request_time', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('request_time', '<=', $this->dateTo);
        }



        try {
            $deletedCount = $query->delete();
            $this->selectedRequests = [];
            $this->selectPage = false;
            $this->alert('success', $deletedCount . ' API requests deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete requests: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.api-requests', [
            'requests' => $this->requests,
            'errorData' => $this->errorData,

        ])->layout('layouts.app', ['title' => 'API Requests']);
    }
}
