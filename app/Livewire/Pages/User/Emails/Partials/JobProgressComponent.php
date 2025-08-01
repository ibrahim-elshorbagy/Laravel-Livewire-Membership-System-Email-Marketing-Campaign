<?php

namespace App\Livewire\Pages\User\Emails\Partials;

use App\Livewire\Pages\User\Emails\EmailListsTable;
use Livewire\Component;
use App\Models\JobProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
class JobProgressComponent extends Component
{
    public $userId;
    private $user;

    // default in ms
    public $pollInterval = 1000;

    public function mount()
    {
        $this->user = auth()->user();
        $this->userId = $this->user->id;

    }

    /**
     * Called via wire:poll
     * We only dispatch an event if something actually changed,
     * to avoid re-rendering the parent for no reason.
     */
    public function refreshProgress()
    {
        $activeJobsExist = $this->checkActiveJobs();

        if ($activeJobsExist != session('active_jobs_flag')) {
            session(['active_jobs_flag' => $activeJobsExist]);
            // Add this line to ensure the event is properly dispatched
            $this->dispatch('jobStatusUpdated', status: $activeJobsExist)->to(EmailListsTable::class);
        }

        // If active jobs remain true, keep poll at 1s; otherwise slow it to 10s
        $this->pollInterval = $activeJobsExist ? 1000 : 10000;

        // Log::info('refreshProgress took '.(microtime(true) - $start).' seconds to complete.');
    }

    protected function checkActiveJobs()
    {
        // Return TRUE if any are still “processing” or “pending”
        $countProcessing = JobProgress::where('user_id', $this->userId)
            ->whereIn('status', ['processing', 'pending'])
            ->count();

        // Also check if user is in the queue
        $queuePosition = $this->queueStatus();

        return ($countProcessing > 0 || $queuePosition > 0);
    }

    #[Computed]
    public function progressData()
    {
        return [
            'progress' => JobProgress::where('user_id', $this->userId)
                ->whereIn('status', ['processing', 'pending'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'queueStatus' => $this->queueStatus(),
        ];
    }

    public function queueStatus()
    {
        // Get the earliest job's created_at for this user
        $userEarliestJob = DB::table('jobs')
            ->where(function ($query) {
                $query->whereRaw("payload LIKE '%\"userId\":{$this->userId}%'")
                    ->orWhereRaw("payload LIKE '%\"user_id\":{$this->userId}%'")
                    ->orWhereRaw("payload LIKE '%i:{$this->userId};%'");
            })
            ->min('created_at');

        if (!$userEarliestJob) {
            return 0;
        }

        // Count how many jobs are ahead of the user's earliest job
        $position = DB::table('jobs')
            ->where('created_at', '<', $userEarliestJob)
            ->count();

        return $position + 1; // Add 1 to account for zero-based position
    }


    public function render()
    {
        return view('livewire.pages.user.emails.partials.job-progress-component', [
            'progressData'  => $this->progressData,
            'pollInterval'  => $this->pollInterval,
        ]);
    }
}
