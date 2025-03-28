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
use Illuminate\Support\Facades\Log;

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


    protected $rules = [
        'message' => 'required|min:1'
    ];

    public function mount(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
        $this->time_zone = auth()->user()->timezone ?? SiteSetting::getValue('APP_TIMEZONE');
        $this->loadInitialConversations();
        $this->WritePermissions();
        if ($this->conversations->isNotEmpty()) {
            $this->lastMessageId = $this->conversations->last()['id'];
        }
    }

    protected function WritePermissions()
    {
        $userRoles = auth()->user()->roles->pluck('name')->toArray();
        $this->isCurrentUserAdmin = in_array('admin', $userRoles);
        $this->isCurrentUserAllowed = $this->isCurrentUserAdmin || (!isset($this->ticket->closed_at) && in_array('user', $userRoles));
    }

    protected function loadInitialConversations()
    {
        $this->conversations = collect($this->ticket->conversations()
            ->with(['user.roles'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'message' => $conversation->message,
                    'created_at' => $conversation->created_at,
                    'user' => [
                        'id' => $conversation->user->id,
                        'first_name' => $conversation->user->first_name,
                        'last_name' => $conversation->user->last_name,
                        'roles' => $conversation->user->roles->toArray(),
                    ],
                ];
            }));
    }

    // This way eager  loading work
    public function pollForNewMessages()
    {
        $newMessages = $this->ticket->conversations()
            ->with(['user.roles'])
            ->where('id', '>', $this->lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'message' => $conversation->message,
                    'created_at' => $conversation->created_at,
                    'user' => [
                        'id' => $conversation->user->id,
                        'first_name' => $conversation->user->first_name,
                        'last_name' => $conversation->user->last_name,
                        'roles' => $conversation->user->roles->toArray(),
                    ],
                ];
            });

        if ($newMessages->isNotEmpty()) {
            $this->conversations = $this->conversations->concat($newMessages);
            $this->lastMessageId = $newMessages->last()['id'];
        }
    }

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
            $userId = $this->ticket->user_id;
            // Store in the same folder structure as logo
            $path = "admin/support/{$userId}/{$fileName}";
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

        return [
            'message' => $message,
            'attachments' => $attachments
        ];
    }


    public function sendMessage()
    {
        $startTime = microtime(true);
        Log::info('Starting message send process');

        $this->validate();
        $user = auth()->user();

        $userRoles = $user->roles->pluck('name')->toArray();

        if ((isset($this->ticket->closed_at)) || !in_array('admin', $userRoles) && $user->id !== $this->ticket->user_id) {
            $this->alert('error', 'You cannot send more messages. This ticket is closed.', ['position' => 'bottom-end']);
            return;
        }

        if ($user->id !== $this->ticket->user_id && !in_array('admin', $userRoles)) {
            $this->alert('error', 'You do not have permission to send messages in this ticket.', ['position' => 'bottom-end']);
            return;
        }
        $purifyStart = microtime(true);
        $cleanMessage = Purifier::clean($this->message, 'youtube');
        Log::info('Message purification took: ' . round((microtime(true) - $purifyStart) * 1000, 2) . 'ms');

        $dbStart = microtime(true);
        // Create conversation with minimal data
        $newConversation = SupportConversation::create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => $user->id,
            'message' => $cleanMessage,
            'created_at' => now()
        ]);
        Log::info('Database operation took: ' . round((microtime(true) - $dbStart) * 1000, 2) . 'ms');

        // Add new message to local collection immediately
        $this->conversations->push([
            'id' => $newConversation->id,
            'message' => $cleanMessage,
            'created_at' => $newConversation->created_at,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                // 'roles' => $user->roles->pluck('name')->toArray(),
                'roles' => $user->roles->toArray(),

            ],
        ]);
        $this->lastMessageId = $newConversation->id;

        // Reset form immediately for better UX
        $this->reset('message');
        $this->dispatch('resetEditor');
        $this->alert('success', 'Message sent successfully.', ['position' => 'bottom-end']);

        Log::info('Total message processing time: ' . round((microtime(true) - $startTime) * 1000, 2) . 'ms');

        // Process email and images in background
        defer(function () use ($user, $cleanMessage,  $userRoles) {
            $emailStart = microtime(true);
            try {
                $processedMessage = $this->processEmailImages($cleanMessage);
                $admin = User::find(1);

                // Ensure we're working with User model instances and check roles properly
                $isAdmin =  in_array('admin', $userRoles);
                $recipientEmail = $isAdmin ? $this->ticket->user->email : $admin->email;
                $recipientName = $isAdmin ? $this->ticket->user->first_name . ' ' . $this->ticket->user->last_name : $admin->name;

                $mailData = [
                    'name' => $recipientName,
                    'email' => $recipientEmail,
                    'subject' => 'Re: ' . $this->ticket->subject,
                    'message' => $processedMessage['message'],
                    'attachments' => $processedMessage['attachments'],
                    'slug' => $isAdmin ? 'support-ticket-admin-response' : 'support-ticket-user-request'
                ];

                Mail::to($recipientEmail)->queue(new BaseSupportMail($mailData));
                Log::info('Email processing and queueing took: ' . round((microtime(true) - $emailStart) * 1000, 2) . 'ms');
            } catch (\Exception $e) {
                Log::error('Failed to process email for support ticket: ' . $e->getMessage());
            }
        });
    }

    public function render()
    {
        return view('livewire.components.support.chat-component', [
            'conversations' => $this->conversations
        ]);
    }
}
