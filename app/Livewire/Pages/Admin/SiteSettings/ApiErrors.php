<?php

namespace App\Livewire\Pages\Admin\SiteSettings;

use App\Models\Admin\Site\ApiError;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ApiErrors extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedErrors = [];
    public $selectPage = false;

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:serverid,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectedErrors' => 'array',
            'selectedErrors.*' => 'integer|exists:api_errors,id',
            'selectPage' => 'boolean',
        ];
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedErrors = $this->errors->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedErrors = [];
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedErrors' => 'required|array|min:1',
            'selectedErrors.*' => 'integer|exists:api_errors,id'
        ]);

        try {
            ApiError::whereIn('id', $this->selectedErrors)->delete();
            $this->selectedErrors = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected errors deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete errors: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function getErrorsProperty()
    {
        return ApiError::when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('serverid', 'like', '%' . $this->search . '%')
                      ->orWhereRaw("JSON_EXTRACT(error_data, '$.error') LIKE ?", ['%' . $this->search . '%'])
                      ->orWhereRaw("JSON_EXTRACT(error_data, '$.message') LIKE ?", ['%' . $this->search . '%']);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function deleteAll()
    {
        try {
            ApiError::truncate();
            $this->selectedErrors = [];
            $this->selectPage = false;
            $this->alert('success', 'All API errors deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete errors: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.api-errors', [
            'errors' => $this->errors
        ])->layout('layouts.app', ['title' => 'API Errors']);
    }
}
