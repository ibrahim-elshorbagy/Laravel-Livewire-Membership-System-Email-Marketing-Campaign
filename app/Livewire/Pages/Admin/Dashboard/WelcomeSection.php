<?php

namespace App\Livewire\Pages\Admin\Dashboard;

use App\Models\Admin\Site\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Mail\EmailVerificationMail;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Mail;

class WelcomeSection extends Component
{


    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        Mail::to($user->email)->queue(new EmailVerificationMail($user));


        Session::flash('status', 'verification-link-sent');
    }
    public function render()
    {
        $user = auth()->user();
        $time_zone = $user->timezone ?? SiteSetting::getValue('APP_TIMEZONE');

        $subscription = $user->lastSubscription();

        $showWarning = Payment::where('user_id', $user->id)
            ->where('status','pending')
            ->latest()
            ->first();

        $subscriptionData = null;
        if ($subscription) {
            $subscriptionData = [
                'plan_id' => $subscription->plan->id,
                'plan_name' => $subscription->plan->name,
                'price' => $subscription->plan->price,
                'periodicity_type' => $subscription->plan->periodicity_type,
                'started_at' => $subscription->started_at->timezone($time_zone)->format('d/m/Y h:i:s A'),
                'expired_at' => $subscription->expired_at->timezone($time_zone)->format('d/m/Y h:i:s A'),
                'remaining_time' => Carbon::parse($subscription->expired_at->timezone($time_zone))->diffForHumans(Carbon::now()->timezone($time_zone), [
                    'parts' => 3,
                    'join' => true,
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                ])
            ];
        }
        return view('livewire.pages.admin.dashboard.welcome-section',[
            'subscription' => $subscriptionData,
            'showWarning'=>$showWarning,

        ]);
    }
}
