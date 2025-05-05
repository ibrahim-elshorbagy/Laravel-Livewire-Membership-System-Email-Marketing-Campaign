<?php

namespace App\Livewire\Pages\User\Report\Email\EmailBounce;

use Livewire\Component;
use App\Models\User\Reports\EmailBounce;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Validator;

class EmailBounceModal extends Component
{
    use LivewireAlert;

    public function saveBounceEmails($emailData)
    {
        try {
            // Validate the incoming data
            $validator = Validator::make(
                ['emailData' => $emailData],
                [
                    'emailData' => 'required|array|min:1',
                    'emailData.*.email' => 'required|string',
                    'emailData.*.type' => 'required|in:soft,hard'
                ],
                [
                    'emailData.required' => 'At least one valid email is required',
                    'emailData.*.type.in' => 'Bounce type must be either soft or hard'
                ]
            );

            if ($validator->fails()) {
                $this->alert('error',' Validation '.$validator->errors()->first(), ['position' => 'bottom-end']);
                return;
            }

            $validated = $validator->validated();

            $data = collect($validated['emailData'])->map(function($item) {
                return [
                    'user_id' => auth()->id(),
                    'email' => $item['email'],
                    'type' => $item['type'],
                    'created_at' => now()
                ];
            })->toArray();

            EmailBounce::insertOrIgnore($data);
            $this->dispatch('refresh-bounce-list');
            $this->dispatch('close-modal', 'add-emails-modal');
            $this->dispatch('reset-emails');

            $this->reset();

            $this->alert('success', count($data) . ' emails added successfully', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            $this->alert('error', $e->getMessage(), ['position' => 'bottom-end']);
            $this->dispatch('keep-modal-open', 'add-emails-modal');
        }
    }

    public function render()
    {
        return view('livewire.pages.user.report.email.email-bounce.email-bounce-modal');
    }
}
