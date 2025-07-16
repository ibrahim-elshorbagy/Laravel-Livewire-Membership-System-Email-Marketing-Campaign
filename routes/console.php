<?php

use App\Models\JobProgress;
use App\Models\Server;
use App\Models\User;
use App\Models\Subscription\Subscription;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\Notification\SubscriptionNotifier;
use App\Services\CampaignRepeaterService;
use Illuminate\Support\Facades\DB;

Schedule::call(function () {
  // Debug info
  Log::channel('worker')->info('Starting subscription check...');

  // Find subscriptions with expired grace periods
  $expiredGraceSubscriptions = Subscription::findWithExpiredGracePeriod()->get();

  // List the subscription IDs being processed
  $subscriptionIds = $expiredGraceSubscriptions->pluck('id')->implode(', ');

  foreach ($expiredGraceSubscriptions as $subscription) {
    $user = $subscription->subscriber;
    if (!$user) {
      continue;
    }

    // Find all servers assigned to this user
    $servers = Server::where('assigned_to_user_id', $user->id)->get();
    $serverCount = $servers->count();

    if ($serverCount > 0) {
      // Update all servers to remove the user assignment
      Server::where('assigned_to_user_id', $user->id)
        ->update(['assigned_to_user_id' => null]);
    }
  }
})
  // ->everyMinute()
  ->daily()
  ->before(function () {
    Log::channel('worker')->info('Starting expired grace period server cleanup...');
  })
  ->after(function () {
    Log::channel('worker')->info('Expired grace period server cleanup completed');
  })
  ->onFailure(function () {
    Log::channel('worker')->error('Expired grace period server cleanup failed');
  });



Schedule::call(function () {
  Log::channel('worker')->info('Cron Works');
});

Schedule::call(function () {
  (new SubscriptionNotifier())->SubscriptionAboutToEndNotify();
})
  ->daily()
  ->before(function () {
    // Log::channel('worker')->info('SubscriptionNotifier work...');
  })
  ->after(function () {
    // Log::channel('worker')->info('SubscriptionNotifier completed successfully....');
  })
  ->onFailure(function () {
    // Log::channel('worker')->error('SubscriptionNotifier failed...');
  })
  ->then(function () {
    // Log::channel('worker')->info('SubscriptionNotifier Closed...');
  });


Schedule::call(function () {
  (new CampaignRepeaterService())->checkAndActivateScheduledCampaigns();
})
  // ->hourly()
  ->everyMinute()
  ->before(function () {
    // Log::channel('repeater')->info('CampaignRepeaterService work...');
  })
  ->after(function () {
    // Log::channel('repeater')->info('CampaignRepeaterService completed successfully....');
  })
  ->onFailure(function () {
    // Log::channel('repeater')->error('CampaignRepeaterService failed...');
  })
  ->then(function () {
    // Log::channel('repeater')->info('CampaignRepeaterService Closed...');
  });


Schedule::call(function () {
  JobProgress::where('status', 'completed')->delete();
});


Schedule::command('queue:work --queue=default,high --tries=5 --stop-when-empty', [])
  ->everyTenSeconds()
  ->withoutOverlapping()
  ->before(function () {
    // Log::channel('worker')->info('Starting queue:work...');
  })
  ->after(function () {
    // Log::channel('worker')->info('Queue worker completed successfully.');
  })
  ->onFailure(function () {
    // Log::channel('worker')->error('Queue worker failed.');
  })
  ->then(function () {
    // Log::channel('worker')->info('Closed queue worker.');
  });
