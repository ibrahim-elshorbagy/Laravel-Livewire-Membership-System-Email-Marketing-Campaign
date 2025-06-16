<?php

namespace App\Livewire\Components\Support;

use App\Jobs\ProcessSupportTicketEmail;
use App\Models\Admin\Support\SupportConversation;
use App\Models\Admin\SupportTicket;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
    public $isCurrentUserAdmin;
    public $isCurrentUserAllowed;

    public $adminTemplate = "dear [customer_name],<br>  <br><br>Best Regards bulkemailapp<br>Support Team";

    protected $rules = [
        'message' => 'required|min:1'
    ];

    public function mount(SupportTicket $ticket)
    {
        $this->ticket = $ticket->load('user.roles');
        $this->time_zone = auth()->user()->timezone ?? config('app.timezone');
        $this->loadConversations();
        $this->determinePermissions();

        // Set initial template for admin
        if ($this->isCurrentUserAdmin) {
            $customerName = $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name;
            $this->message = str_replace('[customer_name]', $customerName, $this->adminTemplate);
        }
    }

    protected function determinePermissions()
    {
        $user = auth()->user();
        $this->isCurrentUserAdmin = $user->hasRole('admin');
        $this->isCurrentUserAllowed = $this->isCurrentUserAdmin ||
            (!$this->ticket->closed_at && $user->hasRole('user'));
    }

    protected function loadConversations()
    {
        $this->conversations = $this->ticket->conversations()
            ->with(['user.roles'])
            ->orderBy('id')
            ->get()
            ->map($this->mapConversation());

        // Use null coalescing with array access
        $this->lastMessageId = $this->conversations->last()['id'] ?? 0;
    }

    public function pollForNewMessages()
    {
        $newMessages = $this->ticket->conversations()
            ->with(['user.roles'])
            ->where('id', '>', $this->lastMessageId)
            ->orderBy('id')
            ->get()
            ->map($this->mapConversation());

        if ($newMessages->isNotEmpty()) {
            $this->conversations = $this->conversations->concat($newMessages);
            $this->lastMessageId = $newMessages->last()['id'];
        }
    }

    protected function mapConversation()
    {
        return fn ($conversation) => [
            'id' => $conversation->id,
            'message' => $conversation->message,
            'created_at' => $conversation->created_at,
            'user' => [
                'id' => $conversation->user->id,
                'first_name' => $conversation->user->first_name,
                'last_name' => $conversation->user->last_name,
                'roles' => $conversation->user->roles->pluck('name')->toArray(),
            ],
        ];
    }


    public function uploadEditorImage($fileData)
    {

        $this->fileData = $fileData;
        try {

            $validatedData =  $this->validate(['fileData' => [
                    'required',
                    'string',
                    'regex:/^data:image\/(jpeg|png|gif|webp);base64,[a-zA-Z0-9\/\+]+={0,2}$/',
                    function ($attribute, $value, $fail) {
                        $size = strlen(base64_decode(explode(',', $value)[1]));
                        if ($size > 5 * 1024 * 1024) {
                            $fail('Image must be less than 5MB');
                        }
                    }
                ]]);

            $image = $validatedData['fileData'];

            // Extract image data
            list($type, $data) = explode(';', $image);
            list(, $data) = explode(',', $data);
            $fileContent = base64_decode($data);
            $imageType = str_replace('data:image/', '', $type);


            // Generate a unique filename
            $fileName = 'support_' . now()->timestamp . '_' . uniqid() . '.' . $imageType;
            $userId = $this->ticket->user_id;
            // Store in the same folder structure as logo
            $path = "admin/support/{$userId}/{$fileName}";
            Storage::disk('local')->put($path, $fileContent);

            $secureUrl = route('chat.image', ['userId' => $userId, 'filename' => $fileName]);

            return [
                'success' => true,
                'url' => $secureUrl,
                'path' => $path
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    public function sendMessage()
    {
        $this->validate();
        $user = auth()->user();

        if (!$this->validatePermissions($user)) {
            return;
        }

        $message = Purifier::clean($this->message, 'youtube');
        $conversation = $this->createConversation($message);
        $this->updateUI($conversation, $user);

        defer(function() use($user,$message){
            $this->ProcessSupportTicketEmail($this->ticket,$user,$message);
        });
    }

    private function ProcessSupportTicketEmail($ticket, $user, $message) {
        if ($user->hasRole('admin')) {
            // If admin, send to ticket user
            $recipientEmail = $this->ticket->user->email;
            $recipientName = $this->ticket->user->first_name . " " . $this->ticket->user->last_name;
            $slug = 'support-ticket-admin-response';
        } else {
            // If user, send to admin email from settings
            $adminEmail = SiteSetting::getValue('mail_from_address');
            $adminName = SiteSetting::getValue('mail_from_name', 'Support Team');
            $recipientEmail = $adminEmail;
            $recipientName = $adminName;
            $slug = 'support-ticket-user-request';
        }

        // $processedMessage = $this->processEmailImages($message);

        $mailData = [
            'subject' => 'User Re: ' . $this->ticket->subject,
            'ticket_id' =>$ticket->id,
            'user_id' =>$ticket->user_id,

            // 'message' => $processedMessage['message'],
            // 'attachments' => $processedMessage['attachments'],
            'slug' => $slug
        ];

        Mail::to($recipientEmail)->queue(new BaseSupportMail($mailData));
    }


    protected function validatePermissions(User $user)
    {
        if ($this->ticket->closed_at ||
            (!$user->hasRole('admin') && $user->id != $this->ticket->user_id)) {
            $this->alert('error', 'Action not allowed', ['position' => 'bottom-end']);
            return false;
        }
        return true;
    }

    protected function createConversation($message)
    {
        return SupportConversation::create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'message' => $message,
            'created_at' => now()
        ]);
    }

    protected function updateUI($conversation, $user)
    {
        $this->conversations->push([
            'id' => $conversation->id,
            'message' => $conversation->message,
            'created_at' => $conversation->created_at,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'roles' => $user->roles->pluck('name')->toArray(),
            ],
        ]);

        $this->lastMessageId = $conversation->id;

        // Reset with template for admin
        if ($this->isCurrentUserAdmin) {
            $customerName = $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name;
            $this->message = str_replace('[customer_name]', $customerName, $this->adminTemplate);
        } else {
            $this->reset('message');
        }

        $this->dispatch('resetEditor', message: $this->message);
        $this->alert('success', 'Message sent', ['position' => 'bottom-end']);
    }

    public function render()
    {
        return view('livewire.components.support.chat-component');
    }
}
