<?php

namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Email\EmailCampaign;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\Rule;

class CampaignList extends Component
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
            'sortField' => ['required', Rule::in(['campaign_title', 'created_at', 'sending_status'])],
            'sortDirection' => ['required', Rule::in(['asc', 'desc'])],
            'perPage' => ['required', 'integer', Rule::in([10, 25, 50])],
            'selectedTemplates' => 'array',
            'selectedTemplates.*' => 'integer|exists:email_campaigns,id',
            'selectPage' => 'boolean',
            'templateId' => 'nullable|integer|exists:email_campaigns,id',
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
            $this->selectedTemplates = $this->campaigns->pluck('id')->map(fn($id) => (string) $id);
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
            'templateId' => 'required|integer|exists:email_campaigns,id'
        ]);

        try {
            $campaign = EmailCampaign::findOrFail($this->templateId);

            if ($campaign->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to template.');
            }

            $campaignsCount = Auth::user()->emailCampaigns()->count();

            if ($campaignsCount === 1) {
                $newStatus = $campaign->sending_status === 'RUN' ? 'PAUSE' : 'RUN';
                $campaign->update(['sending_status' => $newStatus]);
            } else {
                Auth::user()->emailCampaigns()->update(['sending_status' => 'PAUSE']);
                $campaign->update(['sending_status' => 'RUN']);
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
            'templateId' => 'required|integer|exists:email_campaigns,id'
        ]);

        try {
            $campaign = EmailCampaign::findOrFail($this->templateId);

            if ($campaign->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized access to template.');
            }

            $campaign->delete();
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
            'selectedTemplates.*' => 'integer|exists:email_campaigns,id'
        ]);

        try {
            $unauthorizedTemplates = EmailCampaign::whereIn('id', $this->selectedTemplates)
                ->where('user_id', '!=', Auth::id())
                ->exists();

            if ($unauthorizedTemplates) {
                throw new \Exception('Unauthorized access to one or more templates.');
            }

            EmailCampaign::whereIn('id', $this->selectedTemplates)
                ->where('user_id', Auth::id())
                ->delete();

            $this->selectedTemplates = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected templates deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete templates: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    // Campaigns property
    public function getCampaignsProperty()
    {
        return EmailCampaign::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('campaign_title', 'like', '%' . $this->search . '%')
                      ->orWhere('email_subject', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // Render method
    public function render()
    {
        return view('livewire.pages.user.emails.campaign-list', [
            'campaigns' => $this->campaigns
        ])->layout('layouts.app', ['title' => 'Email Campaigns']);
    }
}
