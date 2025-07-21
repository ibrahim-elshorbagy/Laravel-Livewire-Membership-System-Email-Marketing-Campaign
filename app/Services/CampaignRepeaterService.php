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

            // Log::channel('repeater')->info("Campaign {$campaign->id} completed repeat {$repeater->completed_repeats}/{$repeater->total_repeats}");

            // Check if we should create another repeat
            if ($repeater->completed_repeats < $repeater->total_repeats) {
                $this->createNextRepeat($campaign, $repeater);
            } else {
                $this->completeRepeater($repeater);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('repeater')->error("Error processing campaign repeater for campaign {$campaign->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create the next repeat of a campaign - Clone campaign and schedule it for later
     */
    private function createNextRepeat(Campaign $campaign, CampaignRepeater $repeater)
    {
        try {
            // Log::channel('repeater')->info("Creating next repeat for campaign {$campaign->id}, repeater {$repeater->id}");
            // Log::channel('repeater')->info("Repeater interval: {$repeater->interval_hours} hours, interval type: {$repeater->interval_type}");

            // Create new campaign based on the completed one
            $newCampaign = $this->cloneCampaign($campaign, $repeater->completed_repeats + 1);
            // Log::channel('repeater')->info("Cloned campaign created with ID: {$newCampaign->id}");

            // Calculate when the next campaign should start based on interval
            $nextRunAt = now()->addHours((int) $repeater->interval_hours);
            // Log::channel('repeater')->info("Next run time calculated: {$nextRunAt}");

            // Update repeater to point to new campaign and set next run time
            $repeater->update([
                'campaign_id' => $newCampaign->id,
                'next_run_at' => $nextRunAt
            ]);

            // Log::channel('repeater')->info("Repeater {$repeater->id} updated - campaign_id: {$newCampaign->id}, next_run_at: {$nextRunAt}");
            // Log::channel('repeater')->info("Created new repeat campaign {$newCampaign->id} for repeater {$repeater->id}, scheduled to start at {$nextRunAt}");

        } catch (\Exception $e) {
            Log::channel('repeater')->error("Error creating next repeat for campaign {$campaign->id}: " . $e->getMessage());
            Log::channel('repeater')->error("Stack trace: " . $e->getTraceAsString());
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

        // Create new campaign - set to pause and wait for scheduled start time
        $newCampaign = Campaign::create([
            'user_id' => $originalCampaign->user_id,
            'title' => $newTitle,
            'message_id' => $originalCampaign->message_id,
            'status' => Campaign::STATUS_PAUSE, // Start paused, will be activated when interval passes
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
            // Log::channel('repeater')->info("Transferring servers from campaign {$originalCampaign->id} to new campaign {$newCampaign->id}");

            // Get server IDs from the original campaign
            $serverIds = $originalCampaign->servers()->pluck('servers.id')->toArray();
            // Log::channel('repeater')->info("Original campaign {$originalCampaign->id} has servers: " . implode(', ', $serverIds ?: ['none']));

            if (empty($serverIds)) {
                // Log::channel('repeater')->warning("No servers found in original campaign {$originalCampaign->id} to transfer");

                // Check if user has any available servers for the new campaign
                $availableServers = \App\Models\Server::where('assigned_to_user_id', $originalCampaign->user_id)
                    ->pluck('id')
                    ->toArray();

                // Log::channel('repeater')->info("User {$originalCampaign->user_id} has available servers: " . implode(', ', $availableServers ?: ['none']));

                if (!empty($availableServers)) {
                    // Assign user's available servers to new campaign
                    $newCampaign->servers()->sync($availableServers);
                    Log::channel('repeater')->info("Assigned " . count($availableServers) . " available servers to new campaign {$newCampaign->id}");
                } else {
                    // No servers available, pause the new campaign
                    $newCampaign->update(['status' => Campaign::STATUS_PAUSE]);
                    // Log::channel('repeater')->warning("No servers available for new campaign {$newCampaign->id}, setting status to Pause");
                }

                return;
            }

            // Verify servers still exist before transferring
            $existingServerIds = \App\Models\Server::whereIn('id', $serverIds)->pluck('id')->toArray();
            $removedServerIds = array_diff($serverIds, $existingServerIds);

            // Log::channel('repeater')->info("Existing servers that can be transferred: " . implode(', ', $existingServerIds ?: ['none']));

            if (!empty($removedServerIds)) {
                // Log::channel('repeater')->warning("Some servers were deleted and cannot be transferred: " . implode(', ', $removedServerIds));
            }

            if (empty($existingServerIds)) {
                // Log::channel('repeater')->warning("All servers from original campaign {$originalCampaign->id} were deleted, cannot transfer any");
                // Set new campaign to pause since no servers available
                $newCampaign->update(['status' => Campaign::STATUS_PAUSE]);
                return;
            }

            // Remove servers from original campaign (since it's completed)
            $originalCampaign->servers()->detach();
            // Log::channel('repeater')->info("Detached servers from original campaign {$originalCampaign->id}");

            // Attach existing servers to new campaign
            $newCampaign->servers()->sync($existingServerIds);
            // Log::channel('repeater')->info("Transferred " . count($existingServerIds) . " servers from campaign {$originalCampaign->id} to new campaign {$newCampaign->id}");

        } catch (\Exception $e) {
            Log::channel('repeater')->error("Error transferring servers from campaign {$originalCampaign->id} to {$newCampaign->id}: " . $e->getMessage());
            Log::channel('repeater')->error("Stack trace: " . $e->getTraceAsString());
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

    /**
     * Check and activate any paused campaigns that are ready to start based on their scheduled time
     */
    public function checkAndActivateScheduledCampaigns()
    {
        try {
            // Log::channel('repeater')->info('Starting checkAndActivateScheduledCampaigns');

            // First, let's see all repeaters in the system
            // $allRepeaters = CampaignRepeater::with('campaign')->get();
            // Log::channel('repeater')->info('Total repeaters in system: ' . $allRepeaters->count());

            // Let's see active repeaters
            // $activeRepeaters = CampaignRepeater::with('campaign')->where('active', true)->get();
            // Log::channel('repeater')->info('Active repeaters: ' . $activeRepeaters->count());

            // Let's see repeaters with next_run_at set
            // $scheduledRepeaters = CampaignRepeater::with('campaign')
            //     ->where('active', true)
            //     ->whereNotNull('next_run_at')
            //     ->get();
            // Log::channel('repeater')->info('Scheduled repeaters (with next_run_at): ' . $scheduledRepeaters->count());

            // Log each scheduled repeater details
            // foreach ($scheduledRepeaters as $repeater) {
            //     Log::channel('repeater')->info("Repeater {$repeater->id}: next_run_at = {$repeater->next_run_at}, campaign_id = {$repeater->campaign_id}, campaign_status = {$repeater->campaign->status}");
            // }

            // Now check which ones are ready to activate
            $currentTime = now();
            // Log::channel('repeater')->info('Current time: ' . $currentTime);

            // Get all active repeaters with next_run_at in the past
            $readyRepeaters = CampaignRepeater::with('campaign')
                ->where('active', true)
                ->where('next_run_at', '<=', $currentTime)
                ->whereNotNull('next_run_at')
                ->whereHas('campaign', function($query) {
                    $query->where('status', Campaign::STATUS_PAUSE);
                })
                ->get();

            // Log::channel('repeater')->info('Ready repeaters (past next_run_at and paused): ' . $readyRepeaters->count());

            foreach ($readyRepeaters as $repeater) {
                $campaign = $repeater->campaign;

                Log::channel('repeater')->info("Processing repeater {$repeater->id} for campaign {$campaign->id}");
                Log::channel('repeater')->info("Campaign {$campaign->id} has " . $campaign->servers()->count() . " servers and " . $campaign->emailLists()->count() . " email lists");

                // Only activate if the campaign has servers and email lists
                if ($campaign->servers()->count() > 0 && $campaign->emailLists()->count() > 0) {
                    $campaign->update(['status' => Campaign::STATUS_SENDING]);
                    $repeater->update(['next_run_at' => null]); // Clear the scheduled time

                    // Log::channel('repeater')->info("Activated scheduled campaign {$campaign->id} for repeater {$repeater->id}");
                } else {
                    // Log::channel('repeater')->warning("Cannot activate campaign {$campaign->id} - missing servers or email lists");
                }
            }

            // Log::channel('repeater')->info('Completed checkAndActivateScheduledCampaigns');

        } catch (\Exception $e) {
            Log::channel('repeater')->error("Error checking scheduled campaigns: " . $e->getMessage());
            Log::channel('repeater')->error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Create a new clone when user increases total_repeats for a completed repeater
     */
    public function createNewCloneForIncreasedRepeats(CampaignRepeater $repeater)
    {
        try {
            // Log::channel('repeater')->info("User increased total_repeats for completed repeater {$repeater->id}. Creating new clone.");

            // Get the last completed campaign from this repeater
            $lastCampaign = $repeater->campaign;

            // Create new campaign based on the last one
            $newCampaign = $this->cloneCampaign($lastCampaign, $repeater->completed_repeats + 1);


            // Log::channel('repeater')->info("Created new clone campaign {$newCampaign->id} for increased repeater {$repeater->id}, scheduled to start at {$nextRunAt}");

        } catch (\Exception $e) {
            Log::channel('repeater')->error("Error creating new clone for increased repeater {$repeater->id}: " . $e->getMessage());
            Log::channel('repeater')->error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}
