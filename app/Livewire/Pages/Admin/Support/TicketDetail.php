<?php

namespace App\Livewire\Pages\Admin\Support;

use App\Mail\SupportResponseMail;
use App\Models\Admin\SupportTicket;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class TicketDetail extends Component
{
    use LivewireAlert;

    public $ticket;
    public $response;


    public function mount(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
        $cleanMessage = Purifier::clean($ticket->message);
        $this->ticket->message = $cleanMessage;
        $cleanResponse = Purifier::clean($ticket->adamin_response);
        $this->ticket->adamin_response = $cleanResponse;

    }

    protected $rules = [
        'response' => 'required|min:10'
    ];

    public function updateStatus($status)
    {
        $updateData = ['status' => $status];

        if ($status == 'closed') {
            $updateData['closed_at'] = now();
        }else{
            $updateData['closed_at'] = null;
        }

        $this->ticket->update($updateData);

        $this->alert('success', 'Ticket status updated successfully.', ['position' => 'bottom-end']);
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

            $id =$this->ticket->id;
            // Generate a unique filename
            $fileName = 'support_response_'.$id. now()->timestamp . '.' . $imageType;
            $userId = $this->ticket->user_id;
            // Store in the same folder structure as logo
            $path = "admin/support/{$userId}/{$fileName}";
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

    private function processEmailImages($message)
    {
        $attachments = [];
        $storagePath = storage_path('app/public/');

        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $message, $matches);

        foreach ($matches[1] as $imageSrc) {
            // Handle both absolute and relative storage paths
            if (str_contains($imageSrc, '/storage/')) {
                // Convert URL to filesystem path
                $relativePath = str_replace(url('storage/'), '', $imageSrc);
                $relativePath = ltrim(str_replace('/storage/', '', $imageSrc), '/');
                $fullPath = $storagePath . $relativePath;

                // Add debug logging
                // Log::debug('Image processing', [
                //     'src' => $imageSrc,
                //     'relative_path' => $relativePath,
                //     'full_path' => $fullPath,
                //     'exists' => file_exists($fullPath)
                // ]);

                if (file_exists($fullPath)) {
                    $filename = basename($fullPath);

                    // Replace with CID reference
                    $message = str_replace(
                        $imageSrc,
                        'cid:' . $filename,
                        $message
                    );

                    $attachments[] = [
                        'path' => $fullPath,
                        'name' => $filename
                    ];
                }
            }
        }

        // Log::debug('Processed attachments', $attachments);
        return [
            'message' => $message,
            'attachments' => $attachments
        ];
    }


    public function sendResponse()
    {
        $this->validate();

        // Update ticket with response
        $cleanResponse = Purifier::clean($this->response);

        $processedMessage = $this->processEmailImages($cleanResponse);

        $this->ticket->update([
            'admin_response' => $cleanResponse,
            'status' => 'closed',
            'responded_at'=> now(),
            'closed_at' => now()
        ]);

        // Send email to user
        $mailData = [
            'name' => $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name,
            'email' => $this->ticket->user->email,
            'subject' => 'Re: ' . $this->ticket->subject,
            'message' => $processedMessage['message'],
            'attachments' => $processedMessage['attachments']
        ];

        Mail::to($this->ticket->user->email)->queue(new SupportResponseMail($mailData));


        // Reset form and show success message
        $this->reset('response');
        $this->alert('success', 'Response sent successfully.', ['position' => 'bottom-end']);
    }

    public function render()
    {
        return view('livewire.pages.admin.support.ticket-detail', [
            'ticket' => $this->ticket
        ])->layout('layouts.app', ['title' => 'Ticket Detail']);
    }
}
