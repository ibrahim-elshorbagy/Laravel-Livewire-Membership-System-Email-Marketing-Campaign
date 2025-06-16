<?php

namespace App\Livewire\Pages\Profile\Components;

use Livewire\Component;
use App\Models\JobProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmailBouncesProgressBar extends Component
{
    public $userId;
    public $pollInterval = 1000;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function refreshProgress()
    {
        $activeJobsExist = $this->checkActiveJobs();
        // If there's no change in job status, no need to dispatch anything
        if ($activeJobsExist != session('active_jobs_flag')) {
            session(['active_jobs_flag' => $activeJobsExist]);
            // Tell the parent if anything changed
            $this->dispatch('jobStatusUpdated', $activeJobsExist);
        }
        $this->pollInterval = $activeJobsExist ? 1000 : 10000;
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

    public function queueStatus()
    {
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

        return DB::table('jobs')
            ->where('created_at', '<', $userEarliestJob)
            ->count() + 1;
    }

    public function getProgressDataProperty()
    {
        return [
            'progress' => JobProgress::where('user_id', $this->userId)
                ->where('job_type', 'process_bounce_emails')
                ->whereIn('status', ['processing', 'pending', 'failed'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'queueStatus' => $this->queueStatus(),
        ];
    }

    public function render()
    {
        return view('livewire.pages.profile.components.email-bounces-progress-bar', [
            'progressData' => $this->progressData,
            'pollInterval' => $this->pollInterval,
        ]);
    }
}
