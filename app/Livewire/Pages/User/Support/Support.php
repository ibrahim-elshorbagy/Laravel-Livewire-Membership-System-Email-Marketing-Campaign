<?php

namespace App\Livewire\Pages\User\Support;

use App\Mail\BaseSupportMail;
use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportMessage;
use App\Models\User;
use App\Mail\SupportMail;
use App\Models\Admin\Site\SiteSetting;
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

    public $fileData;
    public function uploadEditorImage($fileData)
    {

        $this->fileData = $fileData;
        try {

            $validatedData = $this->validate([
                    'fileData' => [
                        'required',
                        'string',
                        'regex:/^data:image\/(jpeg|png|gif|webp);base64,[a-zA-Z0-9\/\+]+={0,2}$/',
                        // Add file size validation (e.g., max 5MB)
                        function ($attribute, $value, $fail) {
                            $fileSize = strlen(base64_decode(explode(',', $value)[1]));
                            if ($fileSize > 5 * 1024 * 1024) {
                                $fail('The image must not be larger than 5MB.');
                            }
                        }
                    ],
                ]);

            $image = $validatedData['fileData'];

            // Extract image data
            list($type, $data) = explode(';', $image);
            list(, $data) = explode(',', $data);
            $fileContent = base64_decode($data);
            $imageType = str_replace('data:image/', '', $type);


            // Generate a unique filename
            $fileName = 'support_' . now()->timestamp . '_' . uniqid() . '.' . $imageType;
            $userId = auth()->user()->id;
            // Store in the same folder structure as logo
            $path = "users/{$userId}/support/{$fileName}";
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
        $validatedData =$this->validate([
            'subject' => 'required|string|min:3|max:255',
            'message' => 'required|string|min:10',
        ]);


        $cleanMessage = Purifier::clean($this->message, 'youtube');
        $user =auth()->user();

        // Create support ticket
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $this->subject,
            'status' => 'open'
        ]);


        // Create initial conversation
        $ticket->conversations()->create([
            'user_id' => $user->id,
            'message' => $cleanMessage,
            'created_at' => now()
        ]);

        defer(function() use($cleanMessage ,$validatedData,$user,$ticket){
            // Get admin email from settings
            $adminEmail = SiteSetting::getValue('mail_from_address');

            // $processedMessage = $this->processEmailImages($cleanMessage);

            // Prepare mail data
            $mailData = [
                'name' => $user->first_name . " " . $user->last_name,
                'email' => $user->email,
                'subject' => $validatedData['subject'],
                'ticket_id' =>$ticket->id,
                // 'message' => $processedMessage['message'],
                // 'attachments' => $processedMessage['attachments'],
                'slug' => 'support-ticket-user-request'

            ];

            // Send mail
            Mail::to($adminEmail)->queue(new BaseSupportMail($mailData));
        });


        Session::flash('success', 'Message sent successfully.');
        return $this->redirect(route('user.support.tickets'), navigate: true);


    }

    public function render()
    {
        return view('livewire.pages.user.support.support')->layout('layouts.app',['title' => 'Support']);;
    }
}
