<?php

namespace App\Observers\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\EmailListName;
use Illuminate\Support\Facades\Log;

class EmailListNameObserver
{

    public function deleting(EmailListName $emailList)
    {
        // Get and update campaigns before cascade delete happens
        $campaigns = Campaign::whereHas('emailLists', function($query) use ($emailList) {
            $query->where('email_list_id', $emailList->id);
        })
        ->where('status', Campaign::STATUS_SENDING)
        ->get();

        foreach($campaigns as $campaign) {
            // Count email lists excluding the one being deleted
            $remainingLists = $campaign->emailLists()
                ->where('email_list_id', '!=', $emailList->id)
                ->count();

            if ($remainingLists === 0 || !$campaign->servers()->exists()) {
                $campaign->update(['status' => Campaign::STATUS_PAUSE]);
            }
        }
    }


}
