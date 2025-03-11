<?php

namespace App\Livewire\Pages\User\Dashboard;

use Livewire\Component;
use App\Models\Campaign\Campaign;
use App\Models\Payment\Payment;
use App\Models\Server;
use App\Models\Email\EmailMessage;
use App\Models\EmailList;
use App\Models\EmailListName;
use Carbon\Carbon;

class DashboardStatics extends Component
{
    public function refresh()
    {
    }

    public function render()
    {
        $user = auth()->user();
        $subscription = $user->lastSubscription();

        $subscriptionData = null;
        if ($subscription) {
            $subscriptionData = [
                'plan_id' => $subscription->plan->id,
                'plan_name' => $subscription->plan->name,
                'price' => $subscription->plan->price,
                'started_at' => $subscription->created_at->timezone($user->timezone ?? config('app.timezone'))->format('d/m/Y h:i:s A'),
                'expired_at' => $subscription->expired_at->timezone($user->timezone ?? config('app.timezone'))->format('d/m/Y h:i:s A'),
                'remaining_time' => Carbon::parse($subscription->expired_at)->diffForHumans(Carbon::now(), [
                    'parts' => 3,
                    'join' => true,
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                ])
            ];
        }

        // Get user-specific statistics
        $paymentCount = Payment::where('user_id', $user->id)->count();
        $totalPayments = Payment::where('user_id', $user->id)->sum('amount');
        $serverCount = Server::where('assigned_to_user_id', $user->id)->count();
        $totalEmailLists = EmailListName::where('user_id', $user->id)->count();
        $totalEmails = EmailList::whereHas('emailListName', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        $storedMessages = EmailMessage::where('user_id', $user->id)->count();
        $totalCampaigns = Campaign::where('user_id', $user->id)->count();

        return view('livewire.pages.user.dashboard.dashboard-statics', [
            'subscription' => $subscriptionData,
            'paymentCount' => $paymentCount,
            'totalPayments' => $totalPayments,
            'serverCount' => $serverCount,
            'totalEmailLists' => $totalEmailLists,
            'totalEmails' => $totalEmails,
            'storedMessages' => $storedMessages,
            'totalCampaigns' => $totalCampaigns,
        ]);
    }
}
