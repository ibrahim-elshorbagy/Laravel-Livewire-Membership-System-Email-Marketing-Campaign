<?php

namespace App\Livewire\Pages\Admin\Dashboard;

use Carbon\Carbon;
use Livewire\Component;

class WelcomeSection extends Component
{

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
        return view('livewire.pages.admin.dashboard.welcome-section',[
            'subscription' => $subscriptionData,
        ]);
    }
}
