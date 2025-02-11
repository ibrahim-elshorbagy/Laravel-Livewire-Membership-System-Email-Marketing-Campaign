<?php
namespace App\Jobs;

use App\Models\EmailList;
use App\Models\JobProgress;
use App\Traits\TracksProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearEmailStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksProgress;

    protected $userId;
    protected $status;
    protected $isPageAction;
    protected $selectedEmails;

    public function __construct($userId, $status = null, $isPageAction = false, $selectedEmails = [])
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->isPageAction = $isPageAction;
        $this->selectedEmails = $selectedEmails;
        $this->onQueue('high');

        JobProgress::where('user_id', $this->userId)
            ->where('job_type', 'clear_email_status')
            ->where('status', 'processing')
            ->orWhere('status', 'failed')
            ->delete();
    }

    public function handle()
    {
        try {
            // Build the query first to get total count
            $query = EmailList::where('user_id', $this->userId);

            if ($this->isPageAction && !empty($this->selectedEmails)) {
                $query->whereIn('id', $this->selectedEmails);
            }

            if ($this->status) {
                $query->where('status', $this->status);
            }

            $totalCount = $query->count();

            if ($totalCount === 0) {
                $this->initializeProgress('clear_email_status', $this->userId, 0);
                $this->completeProgress();
                return;
            }

            // Initialize progress
            $this->initializeProgress('clear_email_status', $this->userId, $totalCount);

            $processedCount = 0;

            $query->chunkById(1000, function ($chunk) use (&$processedCount) {
                DB::beginTransaction();
                try {
                    EmailList::whereIn('id', $chunk->pluck('id'))->update([
                        'status' => 'NULL',
                        'send_time' => null,
                        'sender_email' => null,
                        'log' => null
                    ]);

                    $processedCount += $chunk->count();
                    $this->updateProgressWithQuota($processedCount);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            });

            $this->completeProgressWithInfo($processedCount);

        } catch (\Exception $e) {
            Log::error('Error in ClearEmailStatus job: ' . $e->getMessage());
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }

    protected function updateProgressWithQuota(int $processedCount)
    {
        if ($this->jobProgress) {
            $this->jobProgress->update([
                'processed_items' => $processedCount,
                'percentage' => min(99, ($processedCount / $this->jobProgress->total_items) * 100),
                'status' => 'processing'
            ]);
        }
    }

    protected function completeProgressWithInfo(int $processedCount)
    {
        $message = "Cleared status for {$processedCount} emails";
        if ($this->status) {
            $message .= " with status '{$this->status}'";
        }

        $this->jobProgress->update([
            'status' => 'completed',
            'processed_items' => $processedCount,
            'percentage' => 100,
            'error' => $message
        ]);

    }
}
