<?php

namespace App\Observers\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\EmailListName;
use Illuminate\Support\Facades\Log;

class EmailListNameObserver
{
    /**
     * Handle the EmailListName "created" event.
     */
    public function created(EmailListName $emailListName): void
    {
        //
    }

    /**
     * Handle the EmailListName "updated" event.
     */
    public function updated(EmailListName $emailListName): void
    {
        //
    }

    /**
     * Handle the EmailListName "deleted" event.
     */
    public function deleting(EmailListName $emailList)
    {
        // Log::info('Email List being deleted', [
        //     'list_id' => $emailList->id,
        //     'list_name' => $emailList->name
        // ]);

        // Get and update campaigns before cascade delete happens
        $campaigns = Campaign::whereHas('emailLists', function($query) use ($emailList) {
            $query->where('email_list_id', $emailList->id);
        })->where('is_active', true)->get();

        foreach($campaigns as $campaign) {
            // Count email lists excluding the one being deleted
            $remainingLists = $campaign->emailLists()
                ->where('email_list_id', '!=', $emailList->id)
                ->count();

            if ($remainingLists === 0 || !$campaign->servers()->exists()) {
                $campaign->update(['is_active' => false]);
                // Log::info('Campaign deactivated due to email list deletion', [
                //     'campaign_id' => $campaign->id,
                //     'list_id' => $emailList->id
                // ]);
            }
        }
    }

    /**
     * Handle the EmailListName "restored" event.
     */
    public function restored(EmailListName $emailListName): void
    {
        //
    }

    /**
     * Handle the EmailListName "force deleted" event.
     */
    public function forceDeleted(EmailListName $emailListName): void
    {
        //
    }
}
