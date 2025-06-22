<?php

namespace App\Services;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRepeater;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignRepeaterService
{
    /**
     * Process a completed campaign with repeater - Check if it should repeat or complete
     */
    public function processCampaignCompletion(Campaign $campaign, CampaignRepeater $repeater)
    {
        try {
            DB::beginTransaction();

            // Increment completed repeats
            $repeater->increment('completed_repeats');

            // Log::info("Campaign {$campaign->id} completed repeat {$repeater->completed_repeats}/{$repeater->total_repeats}");

            // Check if we should create another repeat
            if ($repeater->completed_repeats < $repeater->total_repeats) {
                $this->createNextRepeat($campaign, $repeater);
            } else {
                $this->completeRepeater($repeater);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing campaign repeater for campaign {$campaign->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create the next repeat of a campaign - Clone campaign and start immediately
     */
    private function createNextRepeat(Campaign $campaign, CampaignRepeater $repeater)
    {
        try {
            // Create new campaign based on the completed one
            $newCampaign = $this->cloneCampaign($campaign, $repeater->completed_repeats + 1);

            // Update repeater to point to new campaign
            $repeater->update([
                'campaign_id' => $newCampaign->id
            ]);

            // Log::info("Created new repeat campaign {$newCampaign->id} for repeater {$repeater->id}");

        } catch (\Exception $e) {
            Log::error("Error creating next repeat for campaign {$campaign->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clone a campaign for repeating - Create exact copy with new title and transfer servers
     */
    private function cloneCampaign(Campaign $originalCampaign, int $repeatNumber): Campaign
    {
        // Generate proper title for repeat campaign
        $newTitle = $this->generateRepeatTitle($originalCampaign->title, $repeatNumber);

        // Create new campaign - start sending immediately since repeater is active
        $newCampaign = Campaign::create([
            'user_id' => $originalCampaign->user_id,
            'title' => $newTitle,
            'message_id' => $originalCampaign->message_id,
            'status' => Campaign::STATUS_SENDING, // Start new repeat immediately
        ]);

        // Transfer servers from original campaign to new campaign
        $this->transferServersToNewCampaign($originalCampaign, $newCampaign);

        // Copy email list relationships
        $listIds = $originalCampaign->emailLists()->pluck('email_list_names.id')->toArray();
        $newCampaign->emailLists()->sync($listIds);

        return $newCampaign;
    }

    /**
     * Transfer servers from completed campaign to new repeat campaign
     */
    private function transferServersToNewCampaign(Campaign $originalCampaign, Campaign $newCampaign)
    {
        try {
            // Get server IDs from the original campaign
            $serverIds = $originalCampaign->servers()->pluck('servers.id')->toArray();

            if (empty($serverIds)) {
                // Log::warning("No servers found in original campaign {$originalCampaign->id} to transfer");

                // Check if user has any available servers for the new campaign
                $availableServers = \App\Models\Server::where('assigned_to_user_id', $originalCampaign->user_id)
                    ->pluck('id')
                    ->toArray();

                if (!empty($availableServers)) {
                    // Assign user's available servers to new campaign
                    $newCampaign->servers()->sync($availableServers);
                    // Log::info("Assigned " . count($availableServers) . " available servers to new campaign {$newCampaign->id}");
                } else {
                    // No servers available, pause the new campaign
                    $newCampaign->update(['status' => Campaign::STATUS_PAUSE]);
                    // Log::warning("No servers available for new campaign {$newCampaign->id}, setting status to Pause");
                }

                return;
            }

            // Verify servers still exist before transferring
            $existingServerIds = \App\Models\Server::whereIn('id', $serverIds)->pluck('id')->toArray();
            $removedServerIds = array_diff($serverIds, $existingServerIds);

            if (!empty($removedServerIds)) {
                // Log::warning("Some servers were deleted and cannot be transferred: " . implode(', ', $removedServerIds));
            }

            if (empty($existingServerIds)) {
                // Log::warning("All servers from original campaign {$originalCampaign->id} were deleted, cannot transfer any");
                // Set new campaign to pause since no servers available
                $newCampaign->update(['status' => Campaign::STATUS_PAUSE]);
                return;
            }

            // Remove servers from original campaign (since it's completed)
            $originalCampaign->servers()->detach();

            // Attach existing servers to new campaign
            $newCampaign->servers()->sync($existingServerIds);

            // Log::info("Transferred " . count($existingServerIds) . " servers from campaign {$originalCampaign->id} to new campaign {$newCampaign->id}");

        } catch (\Exception $e) {
            Log::error("Error transferring servers from campaign {$originalCampaign->id} to {$newCampaign->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark repeater as completed - Set inactive and log completion
     */
    private function completeRepeater(CampaignRepeater $repeater)
    {
        $repeater->update([
            'active' => false
        ]);

        // Log::info("Repeater {$repeater->id} completed all {$repeater->total_repeats} repeats");
    }

    /**
     * Generate proper title for repeat campaign using regex to replace existing repeat number
     */
    private function generateRepeatTitle(string $originalTitle, int $repeatNumber): string
    {
        // Check if title already has a repeat pattern like " (Repeat #X)"
        $pattern = '/\s*\(Repeat\s*#\d+\)$/';

        if (preg_match($pattern, $originalTitle)) {
            // Replace existing repeat number with new one
            $newTitle = preg_replace($pattern, " (Repeat #{$repeatNumber})", $originalTitle);
        } else {
            // Add new repeat number
            $newTitle = $originalTitle . " (Repeat #{$repeatNumber})";
        }

        return $newTitle;
    }
}
