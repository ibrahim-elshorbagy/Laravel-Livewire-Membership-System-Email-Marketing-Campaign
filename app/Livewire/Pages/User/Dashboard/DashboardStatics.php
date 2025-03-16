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

    public function getActiveCampaignsProperty()
    {
        $user = auth()->user();
        return Campaign::with(['message', 'emailLists.emails'])
            ->where('user_id', $user->id)
            ->where('status', 'Sending')
            ->get()
            ->map(function ($campaign) {
                $totalEmails = $campaign->emailLists->flatMap(function($list) {
                    return $list->emails;
                })->count();
                $sentEmails = $campaign->emailHistories()->where('status', 'sent')->count();
                $percentage = $totalEmails > 0 ? round(($sentEmails / $totalEmails) * 100, 1) : 0;

                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'total_emails' => $totalEmails,
                    'sent_emails' => $sentEmails,
                    'percentage' => $percentage
                ];
            });
    }

    public function render()
    {
        $user = auth()->user();

        // Get user-specific statistics
        $paymentCount = Payment::where('user_id', $user->id)->where('status', 'approved')->count();
        $totalPayments = Payment::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $serverCount = Server::where('assigned_to_user_id', $user->id)->count();
        $totalEmailLists = EmailListName::where('user_id', $user->id)->count();
        $totalEmails = EmailList::whereHas('emailListName', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        $storedMessages = EmailMessage::where('user_id', $user->id)->count();
        $totalCampaigns = Campaign::where('user_id', $user->id)->count();
        $activeCampaigns = $this->activeCampaigns;

        return view('livewire.pages.user.dashboard.dashboard-statics', [
            'paymentCount' => $paymentCount,
            'totalPayments' => $totalPayments,
            'serverCount' => $serverCount,
            'totalEmailLists' => $totalEmailLists,
            'totalEmails' => $totalEmails,
            'storedMessages' => $storedMessages,
            'totalCampaigns' => $totalCampaigns,
            'activeCampaigns' => $activeCampaigns
        ]);
    }
}
