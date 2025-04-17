<?php
use App\Models\User;
use App\Models\UserBouncesInfo;
use App\Models\JobProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;
use App\Services\BounceMailService;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Jobs\ProcessBounceEmails;
use Livewire\Attributes\On;

new class extends Component
{
    use LivewireAlert;
    public string $bounce_inbox = '';
    public string $bounce_inbox_password = '';
    public string $mail_server = '';
    public string $imap_port = '';
    public int $max_soft_bounces = 0;

    public bool $bounce_status = false;
    public array $bounce_messages = [];
    public ?string $error_message = null;
    private ?BounceMailService $bounceService = null;
    public bool $has_active_job = false;

    #[On('jobStatusUpdated')]
    public function handleJobStatusUpdate($status): void
    {
        $this->has_active_job = $status;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $userBounces = $user->userBouncesInfo ?? new UserBouncesInfo();

        $this->bounce_inbox = $userBounces->bounce_inbox ?? '';
        $this->bounce_inbox_password = $userBounces->bounce_inbox_password ?? '';
        $this->mail_server = $userBounces->mail_server ?? '';
        $this->imap_port = $userBounces->imap_port ?? '993';
        $this->bounce_status = $userBounces->bounce_status ?? false;
        $this->max_soft_bounces = $userBounces->max_soft_bounces ?? 0;

        // Check for active bounce check job
        $this->checkActiveJob();
    }

    protected function checkActiveJob(): void
    {
        $user_id = Auth::id();

        // Check JobProgress table
        $activeJob = JobProgress::where('user_id', $user_id)
            ->where('job_type', 'process_bounce_emails')
            ->whereIn('status', ['processing', 'queued', 'pending'])
            ->exists();

        // Check jobs table
        $activeQueueJob = DB::table('jobs')
            ->where(function ($query) use ($user_id) {
                $query->whereRaw("payload LIKE '%\"userId\":{$user_id}%'")
                    ->orWhereRaw("payload LIKE '%\"user_id\":{$user_id}%'")
                    ->orWhereRaw("payload LIKE '%i:{$user_id};%'");
            })
            ->exists();

        $this->has_active_job = $activeJob || $activeQueueJob;
    }


    public function startBounceCheck()
    {
        try {
            $user = Auth::user();

            // Check for active jobs
            $this->checkActiveJob();

            if ($this->has_active_job) {
                $this->alert('warning', 'A bounce check job is already in progress.', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            $bounceInfo = UserBouncesInfo::where('user_id', $user->id)->first();

            if (!$bounceInfo) {
                $this->alert('error', 'Please save your bounce settings first!', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }



            try {

                $bounceService = new BounceMailService($bounceInfo);

                $connected = $bounceService->testConnection();

                if ($connected) {
                    $this->error_message = null;
                    $this->alert('success', 'Connection test successful! Your IMAP settings are correct.', [
                        'position' => 'bottom-end',
                        'timer' => 3000,
                        'toast' => true,
                    ]);
                }
                $this->error_message =null;

            } catch (Exception $e) {
                // Store the error message for display
                $this->error_message = 'Connection failed: ' . $e->getMessage();

                $this->alert('error', $this->error_message, [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;

            }

            ProcessBounceEmails::dispatch($bounceInfo);
            $this->has_active_job = true;

            $this->alert('success', 'Bounce check job has been queued!', [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);

        } catch (Exception $e) {
            $this->alert('error', 'Failed to queue bounce check job: ' . $e->getMessage(), [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }


    public function stopBounceCheck()
    {
        if ($this->bounceService) {
            $this->bounceService->disconnect();
        }
        $this->bounceService = null;
    }

    public function updateBounceInfo(): void
    {
        $user = Auth::user();

        $rules = [
            'bounce_status' => ['boolean'],
            'bounce_inbox' => ['required', 'email'],
            'bounce_inbox_password' => ['required', 'string'],
            'mail_server' => ['required', 'string'],
            'imap_port' => ['required', 'string'],
            'max_soft_bounces' => ['required', 'integer'],
        ];

        $validated = $this->validate($rules);

        UserBouncesInfo::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $this->alert('success', 'Saved successful!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function testConnection(): void
    {
        try {
            $bounceInfo = new UserBouncesInfo([
                'bounce_inbox' => $this->bounce_inbox,
                'bounce_inbox_password' => $this->bounce_inbox_password,
                'mail_server' => $this->mail_server,
                'imap_port' => $this->imap_port
            ]);

            $bounceService = new BounceMailService($bounceInfo);

            $connected = $bounceService->testConnection();

            if ($connected) {
                $this->error_message = null;
                $this->alert('success', 'Connection test successful! Your IMAP settings are correct.', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
            $this->error_message =null;

        } catch (Exception $e) {
            // Store the error message for display
            $this->error_message = 'Connection test failed: ' . $e->getMessage();

            $this->alert('error', $this->error_message, [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }
}; ?>

<section>
    <form wire:submit="updateBounceInfo">
        <header class="flex flex-col justify-between items-center mb-3 md:flex-row">
            <div class="flex gap-5 items-center mb-6">
                <i class="fa-solid fa-envelope-circle-check fa-2xl"></i>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Email Bounce Settings
                    </h2>

                    <p class="mt-1 text-xs text-gray-600 md:text-sm dark:text-gray-400">
                        Configure bounce email settings to handle undelivered emails
                    </p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 mb-6 max-w-xl">
            <!-- Bounce Inbox -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="bounce_inbox" :value="__('Bounce Inbox')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="bounce_inbox" id="bounce_inbox" class="block mt-1 w-full"
                    placeholder="bounce@localhost" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This is the inbox which emails will be sent to.
                </p>
                <x-input-error :messages="$errors->get('bounce_inbox')" class="mt-2" />
            </div>

            <!-- Bounce Inbox Password -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="bounce_inbox_password" :value="__('Bounce Inbox Password')" /><span
                        class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="bounce_inbox_password" id="bounce_inbox_password" type="password"
                    class="block mt-1 w-full" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This password to access the inbox.
                </p>
                <x-input-error :messages="$errors->get('bounce_inbox_password')" class="mt-2" />
            </div>

            <!-- Mail Server -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="mail_server" :value="__('Mail Server')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="mail_server" id="mail_server" class="block mt-1 w-full"
                    placeholder="mail.localhost" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    This is the domain your email inbox is hosted. Most likely mail.yourdomain.com
                </p>
                <x-input-error :messages="$errors->get('mail_server')" class="mt-2" />
            </div>

            <!-- IMAP Port -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="imap_port" :value="__('IMAP Port')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="imap_port" id="imap_port" class="block mt-1 w-full" placeholder="993" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    The bounce checker requires an IMAP connection. Most IMAP ports are 993.
                </p>
                <x-input-error :messages="$errors->get('imap_port')" class="mt-2" />
            </div>
            <!-- max_soft_bounces -->
            <div>
                <div class="flex gap-1">
                    <x-input-label for="max_soft_bounces" :value="__('Max Soft Bounces')" /><span class="text-red-500">*</span>
                </div>
                <x-text-input wire:model.live="max_soft_bounces" id="max_soft_bounces" class="block mt-1 w-full" placeholder="993" />
                <p class="mt-3 ml-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    Max Soft Bounces to convert to Hard Bounces
                </p>
                <x-input-error :messages="$errors->get('max_soft_bounces')" class="mt-2" />
            </div>
        </div>

        <div class="flex gap-4 items-center mb-6">
            <x-primary-create-button>Save Settings</x-primary-create-button>
            <x-primary-info-button type="button" wire:click="testConnection">
                <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                <span wire:loading wire:target="testConnection" class="flex items-center">
                    <i class="fa-duotone fa-solid fa-spinner fa-spin"></i>
                    <span class="ml-2">Testing...</span>
                </span>
            </x-primary-info-button>

            <x-primary-create-button type="button" wire:click="startBounceCheck" :disabled="$has_active_job" >
                <div class="flex gap-2">
                    <span wire:loading.remove wire:target="startBounceCheck">Start Bounce</span>
                    <span wire:loading wire:target="startBounceCheck" class="flex items-center">
                        <i class="fa-duotone fa-solid fa-spinner fa-spin"></i>
                        <span class="ml-2">Checking...</span>
                    </span>
                </div>
            </x-primary-create-button>

        </div>
    </form>

    <!-- error_message -->
    @if($error_message)
        <div class="p-4 text-sm text-red-800 bg-red-50 rounded-lg dark:bg-neutral-600 dark:text-red-400">
            {{ $error_message }}
        </div>
    @endif

    <!-- Progress Bar Component -->
    @if($has_active_job)
    <livewire:pages.profile.components.email-bounces-progress-bar />
    @endif
</section>
