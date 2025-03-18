<?php

namespace App\Livewire\Pages\Admin\Payment\Offline;

use App\Models\Payment\Offline\OfflinePaymentMethod;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Str;

class OfflinPaymenteMethodsForm extends Component
{
    use WithFileUploads, LivewireAlert;

    public $method_id;
    public $name = '';
    public $instructions = '';
    public $receipt_image = true;
    public $logo = null;
    public $new_logo = null;
    public $logo_preview = null;
    public $slug = '';
    public $active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string',
            'instructions' => 'required|string',
            'receipt_image' => 'boolean',
            'new_logo' => 'nullable|image',
            'slug' => 'required|string',
            'active' => 'boolean'
        ];
    }

    public function mount($method = null)
    {
        if ($method) {
            $this->method_id = $method;
            $methodModel = OfflinePaymentMethod::findOrFail($method);
            $this->fill($methodModel->toArray());
        }
    }

    public function updatedNewLogo()
    {
        try {
            $this->validate([
                'new_logo' => 'image'
            ]);

            $this->logo_preview = $this->new_logo->temporaryUrl();
        } catch (\Exception $e) {
            $this->new_logo = null;
            $this->alert('error', 'Error uploading image: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function savePaymentMethod()
    {
        $validatedData = $this->validate();

        try {
            if ($this->method_id) {
                $method = OfflinePaymentMethod::findOrFail($this->method_id);
                $updateData = [
                    'name' => $validatedData['name'],
                    'slug' => $validatedData['slug'],
                    'instructions' => $validatedData['instructions'],
                    'receipt_image' => $validatedData['receipt_image'],
                    'active' => $validatedData['active']
                ];

                if ($this->new_logo) {
                    if ($method->logo) {
                        Storage::disk('public')->delete($method->logo);
                    }
                    $updateData['logo'] = $this->new_logo->store('payment-methods', 'public');
                }

                $method->update($updateData);

                Session::flash('success', 'Payment method updated successfully!.');

            } else {
                $logoPath = null;
                if ($this->new_logo) {
                    $logoPath = $this->new_logo->store('payment-methods', 'public');
                }

                OfflinePaymentMethod::create([
                    'name' => $validatedData['name'],
                    'slug' => $validatedData['slug'],
                    'instructions' => $validatedData['instructions'],
                    'receipt_image' => $validatedData['receipt_image'],
                    'logo' => $logoPath,
                    'active' => $validatedData['active']

                ]);

                Session::flash('success', 'Payment method created successfully!.');

            }

            return $this->redirect(route('admin.offline-payment-methods'), navigate: true);
        } catch (\Exception $e) {

            Session::flash('success', 'Failed to save payment method!.'.$e->getMessage());

        }
    }

    public function render()
    {
        return view('livewire.pages.admin.payment.offline.offlin-paymente-methods-form')
            ->layout('layouts.app', ['title' => $this->method_id ? 'Edit Payment Method' : 'New Payment Method']);
    }
}
