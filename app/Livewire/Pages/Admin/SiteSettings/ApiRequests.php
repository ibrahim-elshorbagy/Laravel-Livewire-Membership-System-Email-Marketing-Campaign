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

    public $admin_notes;
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

    public function render()
    {
        return view('livewire.pages.admin.site-settings.api-requests', [
            'requests' => $this->requests
        ])->layout('layouts.app', ['title' => 'API Requests']);
    }
}
