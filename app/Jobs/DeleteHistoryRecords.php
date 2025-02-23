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
            $query = EmailHistory::where('campaign_id', $this->campaignId);

            if ($this->type === 'selected') {
                $query->whereIn('id', $this->selectedRecords);
            }

            // Process deletion in chunks
            $query->chunk(1000, function ($records) {
                DB::beginTransaction();
                try {
                    EmailHistory::whereIn('id', $records->pluck('id'))->delete();
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            });

        } catch (\Exception $e) {
            Log::error('Error in DeleteHistoryRecords job: ' . $e->getMessage(), [
                'campaign_id' => $this->campaignId,
                'type' => $this->type
            ]);
            throw $e;
        }
    }
}
