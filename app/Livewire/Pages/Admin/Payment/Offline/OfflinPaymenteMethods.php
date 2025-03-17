<?php

namespace App\Livewire\Pages\Admin\Payment\Offline;

use App\Models\Payment\Offline\OfflinePaymentMethod;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class OfflinPaymenteMethods extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $selectedMethods = [];
    public $selectPage = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'sortField' => 'required|in:name,created_at',
            'sortDirection' => 'required|in:asc,desc',
            'perPage' => 'required|integer|in:10,25,50',
            'selectedMethods' => 'array',
            'selectedMethods.*' => 'integer|exists:offline_payment_methods,id',
            'selectPage' => 'boolean',
        ];
    }

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedMethods = $this->paymentMethods->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedMethods = [];
        }
    }

    public function deletePaymentMethod($methodId)
    {
        try {
            $method = OfflinePaymentMethod::findOrFail($methodId);
            if ($method->logo) {
                Storage::delete($method->logo);
            }
            $method->delete();
            $this->alert('success', 'Payment method deleted successfully!', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete payment method: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function bulkDelete()
    {
        $this->validate([
            'selectedMethods' => 'required|array|min:1',
            'selectedMethods.*' => 'integer|exists:offline_payment_methods,id'
        ]);

        try {
            $methods = OfflinePaymentMethod::whereIn('id', $this->selectedMethods)->get();

            foreach($methods as $method) {
                if ($method->logo) {
                    Storage::delete($method->logo);
                }
                $method->delete();
            }

            $this->selectedMethods = [];
            $this->selectPage = false;
            $this->alert('success', 'Selected payment methods deleted successfully!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete payment methods: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function getPaymentMethodsProperty()
    {
        return OfflinePaymentMethod::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('slug', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.admin.payment.offline.offlin-paymente-methods', [
            'paymentMethods' => $this->paymentMethods,
        ])->layout('layouts.app', ['title' => 'Offline Payment Methods']);
    }
}
