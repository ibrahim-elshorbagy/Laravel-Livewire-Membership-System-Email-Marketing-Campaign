<?php
namespace App\Jobs;

use App\Models\EmailList;
use App\Models\EmailListName;
use App\Models\JobProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LucasDotVin\Soulbscription\Models\Feature;

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
            Log::info("Starting DeleteEmails Job", [
                'user_id' => $this->userId,
                'list_id_input' => $this->listId,
                'isPageAction' => $this->isPageAction,
                'selectedEmails' => $this->selectedEmails
            ]);

            // Resolve list_id from name
            $list = EmailListName::where('name', $this->listId)
                ->where('user_id', $this->userId)
                ->first();

            if (!$list) {
                $this->initializeProgress(0);
                $this->completeProgress("Email list not found.");
                return;
            }

            $realListId = $list->id;

            // Build query to select emails
            $query = EmailList::where('user_id', $this->userId)
                              ->where('list_id', $realListId);

            if ($this->isPageAction && !empty($this->selectedEmails)) {
                $query->whereIn('id', $this->selectedEmails);
            }

            $totalCount = $query->count();

            if ($totalCount === 0) {
                $this->initializeProgress(0);
                $this->completeProgress("No emails to delete.");
                return;
            }

            $this->initializeProgress($totalCount);

            $processedCount = 0;

            $query->chunkById(1000, function ($chunk) use (&$processedCount, $realListId) {
                DB::beginTransaction();
                try {
                    $idsToDelete = $chunk->pluck('id')->toArray();



                    $deleted = EmailList::where('user_id', $this->userId)
                        ->where('list_id', $realListId)
                        ->whereIn('id', $idsToDelete)
                        ->delete();


                    $processedCount += $deleted;

                    DB::commit();

                    if ($this->jobProgress) {
                        $this->jobProgress->update([
                            'processed_items' => $processedCount,
                            'percentage' => min(99, ($processedCount / $this->jobProgress->total_items) * 100),
                            'status' => 'processing'
                        ]);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error during chunk delete: " . $e->getMessage());
                    throw $e;
                }
            });

            // Final update in its own transaction
            DB::transaction(function () use ($processedCount) {
                // Update quota
                $totalEmailCount = EmailList::where('user_id', $this->userId)->count();
                $user = \App\Models\User::find($this->userId);
                $subscribersLimitName = Feature::find(1)?->name;
                $user->forceSetConsumption($subscribersLimitName, (float) $totalEmailCount);

                $this->completeProgress("Successfully deleted {$processedCount} emails from list.");

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
