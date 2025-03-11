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
