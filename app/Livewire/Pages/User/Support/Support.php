<?php

namespace App\Livewire\Pages\User\Support;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportMessage;
use App\Models\User;
use App\Mail\SupportMail;
use App\Models\Admin\SupportTicket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mews\Purifier\Facades\Purifier;

class Support extends Component
{
    use LivewireAlert, WithFileUploads;

    public $name;
    public $email;
    public $subject;
    public $message;

    public function mount()
    {
        // Get current user info
        $this->name = auth()->user()->first_name . ' ' . auth()->user()->last_name;
        $this->email = auth()->user()->email;
    }

    protected $rules = [
        'subject' => 'required|string|min:3|max:255',
        'message' => 'required|string|min:10',
    ];

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
            $fileName = 'support_' . now()->timestamp . '.' . $imageType;
            $userId = auth()->user()->id;
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

    public function sendSupportMessage()
    {
        $this->validate();

        // Extract and process images
        // Log::debug('Original message content', ['message' => $this->message]);


        // Get admin email from settings
        $admin = User::find(1);
        $adminEmail = $admin->email;

        $cleanMessage = Purifier::clean($this->message);
        $processedMessage = $this->processEmailImages($cleanMessage);

        // Create support ticket
        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'subject' => $this->subject,
            'message' => $cleanMessage,
            'status' => 'open'
        ]);

        // Prepare mail data
        $mailData = [
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $processedMessage['message'],
            'attachments' => $processedMessage['attachments']

        ];

        // Send mail
        Mail::to($admin->email)->queue(new SupportMail($mailData));


        Session::flash('success', 'Message sent successfully.');
        return $this->redirect(route('user.support.tickets'), navigate: true);


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

    public function render()
    {
        return view('livewire.pages.user.support.support')->layout('layouts.app',['title' => 'Support']);;
    }
}
