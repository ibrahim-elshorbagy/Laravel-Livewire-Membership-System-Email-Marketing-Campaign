<?php

namespace App\Jobs;

use App\Models\JobProgress;
use App\Models\UserBouncesInfo;
use App\Services\BounceMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\TracksProgress;
use Illuminate\Support\Facades\Log;

class ProcessBounceEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,TracksProgress;

    protected $userBounceInfo;
    public $timeout = 600;
    public $tries = 3;

    public function __construct(UserBouncesInfo $userBounceInfo)
    {
        $this->userBounceInfo = $userBounceInfo;
        $this->cleanupOldProgress();
    }

    protected function cleanupOldProgress()
    {
        JobProgress::where('user_id', $this->userBounceInfo->user_id)
            ->where('job_type', 'process_bounce_emails')
            ->whereIn('status', ['processing', 'failed'])
            ->delete();
    }

    public function handle()
    {
        try {
            $bounceService = new BounceMailService($this->userBounceInfo, $this->job->getJobId());
            $bounceService->connect();
            $bounceService->markUnreadMessages();
            $bounceService->disconnect();
        } catch (\Exception $e) {
            if (isset($bounceService)) {
                try {
                    $bounceService->disconnect();
                } catch (\Exception $disconnectError) {
                    Log::channel('emailBounces')->error('Error disconnecting from IMAP: ' . $disconnectError->getMessage());
                }
            }

            $this->failProgress($e->getMessage());
            Log::channel('emailBounces')->error('Bounce check failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
