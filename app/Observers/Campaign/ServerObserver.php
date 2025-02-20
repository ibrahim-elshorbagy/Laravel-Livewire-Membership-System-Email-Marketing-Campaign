<?php

namespace App\Observers\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Server;
use Illuminate\Support\Facades\Log;

class ServerObserver
{
    /**
     * Handle the Server "created" event.
     */
    public function created(Server $server): void
    {
        //
    }

    /**
     * Handle the Server "updated" event.
     */
    public function updated(Server $server): void
    {

    }

    /**
     * Handle the Server "deleting" event.
     */
    public function deleting(Server $server)
    {
        // Log::info('Server being deleted', [
        //     'server_id' => $server->id,
        //     'server_name' => $server->name
        // ]);

        // Get campaign IDs from campaign_servers table
        $campaignIds = $server->campaignServers()->pluck('campaign_id');

        // Get and update affected campaigns
        Campaign::whereIn('id', $campaignIds)
            ->where('is_active', true)
            ->each(function($campaign) {
                $campaign->update(['is_active' => false]);
                // Log::info('Campaign deactivated due to server deletion', [
                //     'campaign_id' => $campaign->id
                // ]);
            });
    }

    /**
     * Handle the Server "restored" event.
     */
    public function restored(Server $server): void
    {
        //
    }

    /**
     * Handle the Server "force deleted" event.
     */
    public function forceDeleted(Server $server): void
    {
        //
    }
}
