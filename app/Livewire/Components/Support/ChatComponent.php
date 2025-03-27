<?php

namespace App\Livewire\Components\Support;

use App\Models\Admin\Support\SupportConversation;
use App\Models\Admin\SupportTicket;
use Carbon\Carbon;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\BaseSupportMail;
use App\Models\Admin\Site\SiteSetting;

class ChatComponent extends Component
{
    use LivewireAlert;

    public $ticket;
    public $message;
    public $fileData;
    public $time_zone;

    public $conversations;
    public $lastMessageId = 0;


    protected $rules = [
        'message' => 'required|min:1'
    ];

    public function mount(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
        $this->time_zone = $user->timezone ?? SiteSetting::getValue('APP_TIMEZONE');
        $this->loadInitialConversations();

    }

    protected function loadInitialConversations()
    {
        $this->conversations = $this->ticket->conversations()
            ->orderBy('created_at', 'asc')
            ->get();

        if ($this->conversations->isNotEmpty()) {
            $this->lastMessageId = $this->conversations->last()->id;
        }
    }

    public function pollForNewMessages()
    {
        $newMessages = $this->ticket->conversations()
            ->where('id', '>', $this->lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($newMessages->isNotEmpty()) {
            $this->conversations = $this->conversations->merge($newMessages);
            $this->lastMessageId = $newMessages->last()->id;
        }
    }

    public function uploadCKEditorImage($fileData)
    {
        $this->fileData = $fileData;
        try {
            $validatedData = $this->validate([
                'fileData' => ['required', 'string', 'regex:/^data:image\/[a-zA-Z]+;base64,[a-zA-Z0-9\/\+]+={0,2}$/']
            ]);

            $image = $validatedData['fileData'];

            list($type, $data) = explode(';', $image);
            list(, $data) = explode(',', $data);
            $fileContent = base64_decode($data);
            $imageType = str_replace('data:image/', '', $type);

            $id = $this->ticket->id;
            $fileName = 'support_chat_' . $id . now()->timestamp . '.' . $imageType;
            $userId = $this->ticket->user_id;
            $path = "support/chat/{$userId}/{$fileName}";
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


    public function sendMessage()
    {
        $this->validate();

        // Check if user is ticket owner or admin
        $user = auth()->user();

        // Check if ticket is closed
        if ((isset($this->ticket->closed_at)) || !$user->hasRole('admin') && $user->id !== $this->ticket->user_id) {
            $this->alert('error', 'You cannot send more messages. This ticket is closed.', ['position' => 'bottom-end']);
            return;
        }

        if ($user->id !== $this->ticket->user_id && !$user->hasRole('admin')) {
            $this->alert('error', 'You do not have permission to send messages in this ticket.', ['position' => 'bottom-end']);
            return;
        }

        $cleanMessage = Purifier::clean($this->message);

        $newConversation = SupportConversation::create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => $user->id,
            'message' => $cleanMessage,
            'created_at' => now()
        ]);

        defer(function () use($user,$cleanMessage) {

            $processedMessage = $this->processEmailImages($cleanMessage);

            // Determine recipient based on sender
            $admin = User::find(1);
            $recipientEmail = $user->hasRole('admin') ? $this->ticket->user->email : $admin->email;
            $recipientName = $user->hasRole('admin') ? $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name : $admin->name;

            $mailData = [
                'name' => $recipientName,
                'email' => $recipientEmail,
                'subject' => 'Re: ' . $this->ticket->subject,
                'message' => $processedMessage['message'],
                'attachments' => $processedMessage['attachments']
            ];

            // Send email to appropriate recipient
            $mailData['slug'] = $user->hasRole('admin') ? 'support-ticket-admin-response' : 'support-ticket-user-request';

            Mail::to($recipientEmail)->queue(new BaseSupportMail($mailData));

        });
        // Add new message to local collection
        $this->conversations->push($newConversation);
        $this->lastMessageId = $newConversation->id;

        $this->reset('message');
        $this->dispatch('resetEditor');
        $this->alert('success', 'Message sent successfully.', ['position' => 'bottom-end']);
    }

    public function render()
    {
        $conversations = $this->ticket->conversations()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.components.support.chat-component', [
            'conversations' => $conversations
        ]);
    }
}
