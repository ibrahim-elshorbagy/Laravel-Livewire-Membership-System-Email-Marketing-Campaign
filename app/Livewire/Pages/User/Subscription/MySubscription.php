<?php

namespace App\Livewire\Pages\User\Subscription;

use App\Models\Admin\Site\SiteSetting;
use App\Models\Payment\Payment;
use App\Notifications\Paypal\SubscriptionCancelledNotification;
use App\Services\PaypalPaymentService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

use Illuminate\Support\Facades\Mail;
use App\Mail\BaseMail;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Illuminate\Support\Facades\Log;

class MySubscription extends Component
{
    use LivewireAlert, PaypalPaymentService;

    public $subscription;
    private  $user;
    public $isProcessing = false;

    public function mount()
    {
        $this->user = auth()->user();
        $time_zone = auth()->user()->timezone ?? SiteSetting::getValue('APP_TIMEZONE');
        $this->subscription = $this->user->lastSubscription();

        if ($this->subscription) {
            $this->subscription->started_at = $this->subscription->started_at->timezone($time_zone)->toDateTimeString();
            $this->subscription->expired_at = $this->subscription->expired_at->timezone($time_zone)->toDateTimeString();
            $this->subscription->remaining_time = Carbon::parse($this->subscription->expired_at->timezone($time_zone))->diffForHumans(Carbon::now()->timezone($time_zone), [
                'parts' => 3,
                'join' => true,
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            ]);


        }
    }

    public function cancelSubscription()
    {
        $this->isProcessing = true;

        try {
            // Verify webhook first

            DB::beginTransaction();

            $subscription = $this->subscription;


            // Cancel subscription locally
            $subscription->cancel();

            $slug ='user-cancel-subscription';
            $emailTemplate = SystemEmail::where('slug', $slug)->select('id')->first();
            $user = $subscription->subscriber;


            if ($emailTemplate) {

                $mailData = [
                    'slug' => 'user-cancel-subscription',
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                ];
                Mail::to($user->email)->queue(new BaseMail($mailData));

            }else{
                $user->notify(new SubscriptionCancelledNotification($subscription));
            }

            DB::commit();

            $this->alert('success', 'Your subscription has been successfully cancelled.');
            // $this->subscription = $this->user->fresh()->subscription;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to cancel subscription: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }


    public function render()
    {
        return view('livewire.pages.user.subscription.my-subscription')->layout('layouts.app',['title' => 'My Subscription']);
    }
}
