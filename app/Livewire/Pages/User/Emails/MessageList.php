<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Email\EmailMessage;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;

class MessageList extends Component
{
    use WithPagination, LivewireAlert;

    // Define public properties
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedTemplates = [];
    public $selectPage = false;
    public $templateId; // Add this for template operations

    // Define protected query string
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Define rules for properties
    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => ['required', Rule::in(['message_title', 'created_at', 'sending_status'])],
            'sortDirection' => ['required', Rule::in(['asc', 'desc'])],
            'perPage' => ['required', 'integer', Rule::in([10, 25, 50])],
            'selectedTemplates' => 'array',
            'selectedTemplates.*' => 'integer|exists:email_messages,id',
            'selectPage' => 'boolean',
            'templateId' => 'nullable|integer|exists:email_messages,id',
        ];
    }

    // Define validation attributes
    protected $validationAttributes = [
        'search' => 'search term',
        'sortField' => 'sort field',
        'sortDirection' => 'sort direction',
        'perPage' => 'items per page',
        'selectedTemplates' => 'selected templates',
        'templateId' => 'template ID',
    ];

    // Updated method for handling property changes
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Method for updating select page
    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedTemplates = $this->messages->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedTemplates = [];
        }
        $this->validateOnly('selectedTemplates');
    }

    // Method for setting active template
    public function setActiveTemplate($templateId)
    {
        $this->templateId = $templateId;
        $this->validate([
            'templateId' => 'required|integer|exists:email_messages,id'
        ]);

        try {
            $message = EmailMessage::findOrFail($this->templateId);

            if ($message->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to template.');
            }

            $messagesCount = Auth::user()->emailMessages()->count();

            if ($messagesCount === 1) {
                $newStatus = $message->sending_status === 'RUN' ? 'PAUSE' : 'RUN';
                $message->update(['sending_status' => $newStatus]);
            } else {
                Auth::user()->emailMessages()->update(['sending_status' => 'PAUSE']);
                $message->update(['sending_status' => 'RUN']);
            }

            $this->alert('success', 'Template status updated successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update template status: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    // Method for deleting template
    public function deleteTemplate($templateId)
    {
        $this->templateId = $templateId;
        $this->validate([
            'templateId' => 'required|integer|exists:email_messages,id'
        ]);

        try {
            $message = EmailMessage::findOrFail($this->templateId);

            if ($message->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to template.');
            }

            $message->delete();
            $this->alert('success', 'Template deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete template: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    // Method for bulk delete
    public function bulkDelete()
    {
        $this->validate([
            'selectedTemplates' => 'required|array|min:1',
            'selectedTemplates.*' => 'integer|exists:email_messages,id'
        ]);

        try {
            $unauthorizedTemplates = EmailMessage::whereIn('id', $this->selectedTemplates)
                ->where('user_id', '!=', Auth::id())
                ->exists();

            if ($unauthorizedTemplates) {
                throw new \Exception('Unauthorized access to one or more templates.');
            }

            EmailMessage::whereIn('id', $this->selectedTemplates)
                ->where('user_id', Auth::id())
                ->delete();

            $this->selectedTemplates = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected templates deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete templates: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    // Messages property
    public function getMessagesProperty()
    {
        return EmailMessage::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('message_title', 'like', '%' . $this->search . '%')
                      ->orWhere('email_subject', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // Render method
    public function render()
    {
        return view('livewire.pages.user.emails.message-list', [
            'messages' => $this->messages
        ])->layout('layouts.app', ['title' => 'Messages']);
    }
}
