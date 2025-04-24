<?php

namespace App\Livewire\Pages\Admin\SiteSettings\System\SystemEmails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use App\Models\Admin\Site\SystemSetting\SystemEmailList;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class SystemEmailsList extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'updated_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedTemplates = [];
    public $selectPage = false;
    public $templateId;

    #[Url]
    public $selectedList = '';

    public $listName = '';
    public $editingListId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => ['required', Rule::in(['slug', 'updated_at', 'sending_status'])],
            'sortDirection' => ['required', Rule::in(['asc', 'desc'])],
            'perPage' => ['required', 'integer', Rule::in([10, 25, 50])],
            'selectedTemplates' => 'array',
            'selectedTemplates.*' => 'integer|exists:system_emails,id',
            'selectPage' => 'boolean',
            'templateId' => 'nullable|integer|exists:system_emails,id',
            'listName' => ['required', 'string', 'max:255', Rule::unique('system_email_lists', 'name')->ignore($this->editingListId)],
            'editingListId' => ['nullable', 'integer', 'exists:system_email_lists,id'],
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

    public function mount()
    {
        // Set default list to "all" if none selected
        if (empty($this->selectedList)) {
            $this->selectedList = 'all';
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedTemplates = $this->systemEmails->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedTemplates = [];
        }
        $this->validateOnly('selectedTemplates');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelections();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->resetSelections();
    }

    public function updatedSortField()
    {
        $this->resetSelections();
    }

    public function updatedSortDirection()
    {
        $this->resetSelections();
    }

    public function updatedSelectedList()
    {
        $this->resetPage();
        $this->resetSelections();
    }

    protected function resetSelections()
    {
        $this->selectedTemplates = [];
        $this->selectPage = false;
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
        $query = SystemEmail::query();

        if ($this->selectedList !== 'all') {
            $list = SystemEmailList::where('name', $this->selectedList)->first();
            if ($list) {
                $query->where('list_id', $list->id);
            }
        }

        $query->when($this->search, function ($query) {
            $query->where(function($q) {
                $q->orWhere('email_subject', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        });

        return $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage);
    }

    public function getListsProperty()
    {
        return SystemEmailList::withCount('emails')->get();
    }

    public function createList()
    {
        $this->validate([
            'listName' => 'required|string|max:255|unique:system_email_lists,name'
        ]);

        try {
            SystemEmailList::create([
                'name' => $this->listName
            ]);

            $this->listName = '';
            $this->dispatch('close-modal', 'create-list');
            $this->alert('success', 'List created successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to create list.', ['position' => 'bottom-end']);
        }
    }


    public function updateList()
    {
        $this->validate([
            'editingListId' => 'required|exists:system_email_lists,id',
            'listName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('system_email_lists', 'name')->ignore($this->editingListId)
            ]
        ]);

        try {
            SystemEmailList::findOrFail($this->editingListId)
                ->update(['name' => $this->listName]);

            $this->listName = '';
            $this->editingListId = null;
            $this->dispatch('close-modal', 'edit-list-modal');
            $this->alert('success', 'List updated successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update list.', ['position' => 'bottom-end']);
        }
    }

    public function deleteList($listId)
    {
        try {
            $list = SystemEmailList::findOrFail($listId);
            $list->delete();
            $this->alert('success', 'List deleted successfully!', ['position' => 'bottom-end']);

            if ($this->selectedList === $list->name) {
                $this->selectedList = 'all';
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete list.', ['position' => 'bottom-end']);
        }
    }


    public $assignListId = null;
    public $selectedEmailsForList = [];

    public function openAssignListModal()
    {
        $this->validate([
            'selectedTemplates' => 'required|array|min:1',
            'selectedTemplates.*' => 'integer|exists:system_emails,id'
        ]);

        $this->selectedEmailsForList = $this->selectedTemplates;
        $this->assignListId = null;
        $this->dispatch('open-modal', 'assign-list-modal');
    }

    public function assignToList()
    {
        $this->validate([
            'assignListId' => 'required|exists:system_email_lists,id',
            'selectedEmailsForList' => 'required|array|min:1',
            'selectedEmailsForList.*' => 'integer|exists:system_emails,id'
        ]);

        try {
            SystemEmail::whereIn('id', $this->selectedEmailsForList)
                ->update(['list_id' => $this->assignListId]);

            $this->assignListId = null;
            $this->selectedEmailsForList = [];
            $this->selectedTemplates = [];
            $this->selectPage = false;
            $this->dispatch('close-modal', 'assign-list-modal');
            $this->alert('success', 'Templates assigned to list successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to assign templates to list: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function removeFromList($templateId = null)
    {
        try {
            $query = SystemEmail::query();

            if ($templateId) {
                $query->where('id', $templateId);
            } else {
                $this->validate([
                    'selectedTemplates' => 'required|array|min:1',
                    'selectedTemplates.*' => 'integer|exists:system_emails,id'
                ]);
                $query->whereIn('id', $this->selectedTemplates);
            }

            $query->update(['list_id' => null]);

            if (!$templateId) {
                $this->selectedTemplates = [];
                $this->selectPage = false;
            }

            $this->alert('success', 'Templates removed from list successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to remove templates from list: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.system.system-emails.system-emails-list', [
            'systemEmails' => $this->systemEmails,
            'lists' => $this->lists
        ])->layout('layouts.app', ['title' => 'System Emails']);
    }
}
