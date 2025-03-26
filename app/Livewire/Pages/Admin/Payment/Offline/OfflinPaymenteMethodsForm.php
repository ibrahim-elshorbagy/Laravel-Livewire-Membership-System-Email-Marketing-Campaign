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
    public $temp_folder;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'instructions' => 'required|string',
            'receipt_image' => 'boolean',
            'new_logo' => 'nullable|image',
            'slug' => 'required|string|max:255',
            'active' => 'boolean'
        ];
    }

    public function mount($method = null)
    {
        if ($method) {
            $this->method_id = $method;
            $methodModel = OfflinePaymentMethod::findOrFail($method);
            $this->fill($methodModel->toArray());
            $this->temp_folder = $method;
        } else {
            // Generate a random folder name for new methods
            $this->temp_folder =  "offline-payment-temp-" . now()->timestamp;
        }
    }

    public $fileData;
    public function uploadCKEditorImage($fileData)
    {

        $this->fileData = $fileData;
        try {

            $validatedData = $this->validate([
                'fileData' => ['required', 'string', 'regex:/^data:image\/[a-zA-Z]+;base64,[a-zA-Z0-9\/\+]+={0,2}$/'],
            ]);

            $image = $validatedData['fileData'];

            // Extract image data
            list($type, $data) = explode(';', $image);
            list(, $data) = explode(',', $data);
            $fileContent = base64_decode($data);
            $imageType = str_replace('data:image/', '', $type);


            // Generate a unique filename
            $fileName = 'editor_' . Str::random(10) . '.' . $imageType;

            // Store in the same folder structure as logo
            $path = "admin/offline-payment-methods/{$this->temp_folder}/{$fileName}";
            Storage::disk('public')->put($path, $fileContent);

            return [
                'success' => true,
                'url' => Storage::url($path),
                'path' => $path
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
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
                    // Store logo in the same folder structure
                    $logoPath = $this->new_logo->storeAs(
                        "admin/offline-payment-methods/{$this->temp_folder}",
                        "logo_" . time() . "." . $this->new_logo->getClientOriginalExtension(),
                        'public'
                    );
                    $updateData['logo'] = $logoPath;
                }

                $method->update($updateData);
                Session::flash('success', 'Payment method updated successfully!');

            } else {
                $logoPath = null;
                if ($this->new_logo) {
                    // Store logo in the same folder structure
                    $logoPath = $this->new_logo->storeAs(
                        "admin/offline-payment-methods/{$this->temp_folder}",
                        "logo_" . time() . "." . $this->new_logo->getClientOriginalExtension(),
                        'public'
                    );
                }

                $newMethod = OfflinePaymentMethod::create([
                    'name' => $validatedData['name'],
                    'slug' => $validatedData['slug'],
                    'instructions' => $validatedData['instructions'],
                    'receipt_image' => $validatedData['receipt_image'],
                    'logo' => $logoPath,
                    'active' => $validatedData['active']
                ]);

                Session::flash('success', 'Payment method created successfully!');
            }

            return $this->redirect(route('admin.offline-payment-methods'), navigate: true);
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to save payment method: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.payment.offline.offlin-paymente-methods-form')
            ->layout('layouts.app', ['title' => $this->method_id ? 'Edit Payment Method' : 'New Payment Method']);
    }
}