<?php

namespace App\Livewire\Pages\Admin\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Models\Payment\Payment;
use App\Models\Server;
use App\Models\EmailList;
use App\Models\EmailListName;
use App\Models\Email\EmailMessage;
use LucasDotVin\Soulbscription\Models\Subscription;
use Carbon\Carbon;

class DashboardStatics extends Component
{

    public function refresh()
    {
    }
    public function render()
    {
        $totalUsers = User::count();

        // Get active subscriptions excluding trial plan (id=1)
        $activeSubscriptions = Subscription::whereNull('canceled_at')
            ->whereHas('plan', function($query) {
                $query->where('id', '!=', 1);
            })
            ->count();

        $totalCampaigns = Campaign::count();

        $paymentCount = Payment::where('status', 'approved')->count();
        $totalPayments = Payment::where('status', 'approved')->sum('amount');

        // New statistics
        $serverCount = Server::count();
        $totalEmailLists = EmailListName::count();
        $totalEmails = EmailList::count();
        $storedMessages = EmailMessage::count();

        return view('livewire.pages.admin.dashboard.dashboard-statics', [
            'totalUsers' => $totalUsers,
            'activeSubscriptions' => $activeSubscriptions,
            'totalCampaigns' => $totalCampaigns,
            'totalPayments' => $totalPayments,
            'serverCount' => $serverCount,
            'paymentCount' => $paymentCount,
            'totalEmailLists' => $totalEmailLists,
            'totalEmails' => $totalEmails,
            'storedMessages' => $storedMessages,
        ]);
    }
}
