<?php

namespace App\Observers\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Server;
use Illuminate\Support\Facades\Log;

class ServerObserver
{
    /**
     * Handle server deletion - Transfer servers from deleted server's campaigns to repeater campaigns if applicable
     */
    public function deleting(Server $server)
    {
        // Get campaign IDs from campaign_servers table
        $campaignIds = $server->campaignServers()->pluck('campaign_id');

        // Get affected campaigns that are not completed
        $affectedCampaigns = Campaign::whereIn('id', $campaignIds)
            ->where('status', Campaign::STATUS_SENDING)
            ->get();

        foreach ($affectedCampaigns as $campaign) {
            // Check if campaign has an active repeater
            $repeater = $campaign->repeater;

            if ($repeater && $repeater->active) {
                // If campaign has active repeater, pause it instead of completing
                // The repeater will handle creating new campaign later
                $campaign->update(['status' => Campaign::STATUS_PAUSE]);
                // Log::info("Paused campaign {$campaign->id} with active repeater due to server {$server->id} deletion");
            } else {
                // No repeater, just pause the campaign
                $campaign->update(['status' => Campaign::STATUS_PAUSE]);
                // Log::info("Paused campaign {$campaign->id} due to server {$server->id} deletion");
            }
        }

        // Log server deletion impact
        if ($affectedCampaigns->count() > 0) {
            Log::warning("Server {$server->id} deletion affected " . $affectedCampaigns->count() . " active campaigns");
        }
    }
}
