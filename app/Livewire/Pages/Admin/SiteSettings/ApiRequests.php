<?php

namespace App\Livewire\Pages\Admin\SiteSettings;

use App\Models\Admin\Site\ApiRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ApiRequests extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'request_time';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedRequests = [];
    public $selectPage = false;

    protected function rules()
    {
        return [
            'search' => 'nullable|string',
            'sortField' => 'required|in:serverid,request_time,execution_time,status',
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

    public function getRequestsProperty()
    {
        return ApiRequest::when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('serverid', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
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
