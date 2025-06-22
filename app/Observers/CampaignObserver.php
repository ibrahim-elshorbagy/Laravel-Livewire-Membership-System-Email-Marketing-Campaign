<?php

namespace App\Observers;

use App\Models\Campaign\Campaign;
use App\Services\CampaignRepeaterService;
use Illuminate\Support\Facades\Log;

class CampaignObserver
{
    /**
     * Handle the Campaign "updated" event - Check if campaign status changed to completed
     */
    public function updated(Campaign $campaign)
    {
        // Check if the campaign status was changed to 'Completed'
        if ($campaign->isDirty('status') && $campaign->status === Campaign::STATUS_COMPLETED) {
            $this->handleCampaignCompletion($campaign);
        }
    }

    /**
     * Process campaign completion and handle repeater logic
     */
    private function handleCampaignCompletion(Campaign $campaign)
    {
        try {
            // Check if campaign has an active repeater
            $repeater = $campaign->repeater;

            if (!$repeater || !$repeater->active) {
                // Log::info("Campaign {$campaign->id} completed without active repeater");
                return;
            }

            // Use the service to handle repeater logic
            $campaignRepeaterService = new CampaignRepeaterService();
            $campaignRepeaterService->processCampaignCompletion($campaign, $repeater);

        } catch (\Exception $e) {
            Log::error("Error processing campaign completion for campaign {$campaign->id}: " . $e->getMessage());
        }
    }
}
