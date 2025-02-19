<?php
namespace App\Jobs;

use App\Models\EmailList;
use App\Models\JobProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $listId;
    protected $isPageAction;
    protected $selectedEmails;
    protected $jobProgress;

    public function __construct($userId, $listId, $isPageAction = false, $selectedEmails = [])
    {
        $this->userId = $userId;
        $this->listId = $listId;
        $this->isPageAction = $isPageAction;
        $this->selectedEmails = $selectedEmails;
        $this->onQueue('high');

        JobProgress::where('user_id', $this->userId)
            ->where('job_type', 'delete_emails')
            ->where('status', 'processing')
            ->orWhere('status', 'failed')
            ->delete();
    }

    protected function initializeProgress($totalCount)
    {
        $this->jobProgress = JobProgress::create([
            'job_id' => $this->job->getJobId(),
            'job_type' => 'delete_emails',
            'user_id' => $this->userId,
            'total_items' => $totalCount,
            'processed_items' => 0,
            'status' => 'processing',
            'percentage' => 0
        ]);
    }

    public function handle()
    {
        try {
            // Get initial count and setup progress
            $query = EmailList::where('user_id', $this->userId)
                            ->where('list_id', $this->listId);

            if ($this->isPageAction && !empty($this->selectedEmails)) {
                $query->whereIn('id', $this->selectedEmails);
            }

            $totalCount = $query->count();

            if ($totalCount === 0) {
                $this->initializeProgress(0);
                $this->completeProgress("No emails to delete");
                return;
            }

            // Initialize progress before transaction
            $this->initializeProgress($totalCount);

            $processedCount = 0;

            // Process deletion in chunks
            $query->chunkById(1000, function ($chunk) use (&$processedCount) {
                DB::beginTransaction();
                try {
                    // Make sure to include list_id in the deletion query
                    EmailList::where('list_id', $this->listId)
                            ->whereIn('id', $chunk->pluck('id'))
                            ->delete();

                    $processedCount += $chunk->count();

                    // Update progress outside transaction
                    DB::commit();

                    // Update progress after successful commit
                    if ($this->jobProgress) {
                        $this->jobProgress->update([
                            'processed_items' => $processedCount,
                            'percentage' => min(99, ($processedCount / $this->jobProgress->total_items) * 100),
                            'status' => 'processing'
                        ]);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            });

            // Final update in its own transaction
            DB::transaction(function () use ($processedCount) {
                // Update quota
                $totalEmailCount = EmailList::where('user_id', $this->userId)->count();
                $user = \App\Models\User::find($this->userId);
                $user->forceSetConsumption('Subscribers Limit', (float) $totalEmailCount);

                // Complete the progress
                $this->completeProgress("Successfully deleted {$processedCount} emails from list {$this->listId}");
            });

        } catch (\Exception $e) {
            Log::error('Error in DeleteEmails job: ' . $e->getMessage());
            if ($this->jobProgress) {
                $this->jobProgress->update([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    protected function completeProgress($message)
    {
        if ($this->jobProgress) {
            $this->jobProgress->update([
                'status' => 'completed',
                'processed_items' => $this->jobProgress->total_items,
                'percentage' => 100,
                'error' => $message
            ]);
        }
    }
}
