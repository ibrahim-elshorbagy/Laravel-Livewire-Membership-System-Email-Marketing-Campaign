<?php

namespace App\Jobs;

use App\Models\Campaign\EmailHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteHistoryRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignId;
    protected $type;
    protected $selectedRecords;

    public function __construct($campaignId, $type = 'all', $selectedRecords = [])
    {
        $this->campaignId = $campaignId;
        $this->type = $type;
        $this->selectedRecords = $selectedRecords;
        $this->onQueue('high');
    }

    public function handle()
    {
        try {
            // Build initial query
            $query = EmailHistory::where('campaign_id', $this->campaignId);

            if ($this->type === 'selected') {
                $query->whereIn('id', $this->selectedRecords);
            }

            $totalCount = $query->count();

            if ($totalCount === 0) {
                // Log::info('No history records to delete', [
                //     'campaign_id' => $this->campaignId,
                //     'type' => $this->type
                // ]);
                return;
            }

            // Process deletion in chunks
            $query->chunkById(1000, function ($chunk) {
                DB::beginTransaction();
                try {
                    EmailHistory::where('campaign_id', $this->campaignId)
                        ->whereIn('id', $chunk->pluck('id'))
                        ->delete();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            });

            // Log::info('Successfully deleted history records', [
            //     'campaign_id' => $this->campaignId,
            //     'type' => $this->type,
            //     'total_deleted' => $totalCount
            // ]);

        } catch (\Exception $e) {
            Log::error('Error in DeleteHistoryRecords job: ' . $e->getMessage(), [
                'campaign_id' => $this->campaignId,
                'type' => $this->type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

}
