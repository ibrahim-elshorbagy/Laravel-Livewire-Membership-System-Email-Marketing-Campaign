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

class ClearEmailStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $status;
    protected $isPageAction;
    protected $selectedEmails;



    public function __construct($userId, $status = null, $isPageAction = false, $selectedEmails = [])
    {
        $this->userId = $userId;
        $this->status = $status;
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

                if ($this->status) {
                    $query->where('status', $this->status);
                }

                $count = $query->count();

                if ($count === 0) {
                    return;
                }

                $query->chunkById(1000, function ($chunk) {
                    EmailList::whereIn('id', $chunk->pluck('id'))->update([
                        'status' => 'NULL',
                        'send_time' => null,
                        'sender_email' => null,
                        'log' => null
                    ]);
                });
            });
        } catch (\Exception $e) {
            Log::error('Error in ClearEmailStatus job: ' . $e->getMessage());
            throw $e;
        }
    }
}

