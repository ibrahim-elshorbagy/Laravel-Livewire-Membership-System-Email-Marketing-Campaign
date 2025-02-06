<?php

namespace App\Jobs;

use App\Models\EmailList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $isPageAction;
    protected $selectedEmails;

    public function __construct($userId, $isPageAction = false, $selectedEmails = [])
    {
        $this->userId = $userId;
        $this->isPageAction = $isPageAction;
        $this->selectedEmails = $selectedEmails;
        $this->onQueue('high');

    }

    public function handle()
    {
        try {
            DB::transaction(function () {
                $query = EmailList::where('user_id', $this->userId);

                if ($this->isPageAction && !empty($this->selectedEmails)) {
                    $query->whereIn('id', $this->selectedEmails);
                }

                $count = $query->count();

                if ($count === 0) {
                    return;
                }

                $query->chunkById(1000, function ($chunk) {
                    EmailList::whereIn('id', $chunk->pluck('id'))->delete();
                });

                $totalEmailCount = EmailList::where('user_id', $this->userId)->count();
                $user = \App\Models\User::find($this->userId);
                $user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
            });
        } catch (\Exception $e) {
            Log::error('Error in DeleteEmails job: ' . $e->getMessage());
            throw $e;
        }
    }
}

