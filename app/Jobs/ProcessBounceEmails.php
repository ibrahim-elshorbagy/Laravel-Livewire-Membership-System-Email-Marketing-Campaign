<?php

namespace App\Jobs;

use App\Models\EmailList;
use App\Models\UserBouncesInfo;
use App\Services\BounceMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBounceEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userBounceInfo;

    public function __construct(UserBouncesInfo $userBounceInfo)
    {
        $this->userBounceInfo = $userBounceInfo;
    }

    public function handle()
    {
        try {
            $bounceService = new BounceMailService($this->userBounceInfo);
            $bounceService->connect();

            $messages = $bounceService->getUnreadMessages();

            foreach ($messages as $message) {
                if (!empty($message['affected_email'])) {
                    $emailList = EmailList::where('user_id', $this->userBounceInfo->user_id)
                        ->where('email', $message['affected_email'])
                        ->first();

                    if ($emailList) {
                        if ($message['bounce_type'] === 'hard') {
                            $emailList->is_hard_bounce = true;
                            $emailList->save();
                        } elseif ($message['bounce_type'] === 'soft') {
                            $emailList->soft_bounce_counter++;

                            // Check if soft bounce count exceeds max limit
                            if ($emailList->soft_bounce_counter >= $this->userBounceInfo->max_soft_bounces) {
                                $emailList->is_hard_bounce = true;
                            }

                            $emailList->save();
                        }
                    }
                }
            }

            $bounceService->disconnect();
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            report($e);
        }
    }
}
