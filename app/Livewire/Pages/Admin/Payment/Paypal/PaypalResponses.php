<?php

namespace App\Livewire\Pages\Admin\Payment\Paypal;

use App\Models\Payment\Paypal\PaypalResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Computed;

class PaypalResponses extends Component
{
    use WithPagination, LivewireAlert;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedResponses = [];
    public $selectPage = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:transaction_id,status,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectedResponses' => 'array',
            'selectedResponses.*' => 'integer|exists:paypal_responses,id',
            'selectPage' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedResponses = $this->responses->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedResponses = [];
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedResponses' => 'required|array|min:1',
            'selectedResponses.*' => 'integer|exists:paypal_responses,id'
        ]);

        try {
            PaypalResponse::whereIn('id', $this->selectedResponses)->delete();
            $this->selectedResponses = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected responses deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete responses: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    #[Computed]
    public function responses()
    {
        return PaypalResponse::with('user')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('transaction_id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function($userQuery) {
                          $userQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$this->search}%")
                                   ->orWhere('email', 'like', '%' . $this->search . '%')
                                   ->orWhere('username', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function deleteAll()
    {
        try {
            PaypalResponse::truncate();
            $this->selectedResponses = [];
            $this->selectPage = false;
            $this->alert('success', 'All PayPal responses deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete responses: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.payment.paypal.paypal-responses', [
            'responses' => $this->responses
        ])->layout('layouts.app', ['title' => 'PayPal Responses']);
    }
}
