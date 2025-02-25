<?php

namespace App\Observers\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Server;
use Illuminate\Support\Facades\Log;

class ServerObserver
{

    public function deleting(Server $server)
    {
        // Get campaign IDs from campaign_servers table
        $campaignIds = $server->campaignServers()->pluck('campaign_id');

        // Get and update affected campaigns that are not completed
        Campaign::whereIn('id', $campaignIds)
            ->where('status', Campaign::STATUS_SENDING)
            ->each(function($campaign) {
                $campaign->update(['status' => Campaign::STATUS_PAUSE]);
            });
    }

}
