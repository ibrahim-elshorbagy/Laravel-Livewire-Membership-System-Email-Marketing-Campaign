<?php

namespace App\Livewire\Pages\Admin\SiteSettings\System\SystemEmails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;

class SystemEmailsList extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedTemplates = [];
    public $selectPage = false;
    public $templateId;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => ['required', Rule::in(['message_title', 'created_at', 'sending_status'])],
            'sortDirection' => ['required', Rule::in(['asc', 'desc'])],
            'perPage' => ['required', 'integer', Rule::in([10, 25, 50])],
            'selectedTemplates' => 'array',
            'selectedTemplates.*' => 'integer|exists:system_emails,id',
            'selectPage' => 'boolean',
            'templateId' => 'nullable|integer|exists:system_emails,id',
        ];
    }

    protected $validationAttributes = [
        'search' => 'search term',
        'sortField' => 'sort field',
        'sortDirection' => 'sort direction',
        'perPage' => 'items per page',
        'selectedTemplates' => 'selected templates',
        'templateId' => 'template ID',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedTemplates = $this->systemEmails->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedTemplates = [];
        }
        $this->validateOnly('selectedTemplates');
    }


    public function deleteTemplate($templateId)
    {
        $this->templateId = $templateId;
        $this->validate([
            'templateId' => 'required|integer|exists:system_emails,id'
        ]);

        try {
            SystemEmail::findOrFail($this->templateId)->delete();
            $this->alert('success', 'Template deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete template: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedTemplates' => 'required|array|min:1',
            'selectedTemplates.*' => 'integer|exists:system_emails,id'
        ]);

        try {
            SystemEmail::whereIn('id', $this->selectedTemplates)->delete();
            $this->selectedTemplates = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected templates deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete templates: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function getSystemEmailsProperty()
    {
        return SystemEmail::when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('message_title', 'like', '%' . $this->search . '%')
                      ->orWhere('email_subject', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.system.system-emails.system-emails-list', [
            'systemEmails' => $this->systemEmails
        ])->layout('layouts.app', ['title' => 'System Emails']);
    }
}
