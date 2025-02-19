<?php

namespace App\Livewire\Pages\User\Emails\Partials;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\JobProgress;
use Illuminate\Support\Facades\DB;

class JobProgressComponent extends Component
{
    public $user;
    // Set a default polling interval in milliseconds
    public $pollInterval = 1000; // 1 second by default

    public function mount()
    {
        $this->user = auth()->user();
    }

    /**
     * This method will be called via polling.
     * It computes job progress, dispatch an event to update the parent,
     * and adjusts the polling interval.
     */
    public function updateJobProgress()
    {
        $jobProgress = $this->JobProgress; // Computed property below

        // Determine if there are any active jobs.
        $activeJobsExist = $jobProgress['progress']->isNotEmpty() || $jobProgress['queueStatus'] > 0;

        // dispatch the status so that the parent can update its flag.
        $this->dispatch('jobStatusUpdated', $activeJobsExist);

        // Dynamically adjust polling interval: faster when active, slower when not.
        $this->pollInterval = $activeJobsExist ? 1000 : 10000;
    }

    #[Computed()]
    public function JobProgress()
    {
        return [
            'progress'    => JobProgress::where('user_id', $this->user->id)
                                ->whereIn('status', ['processing', 'pending'])
                                ->orderBy('created_at', 'desc')
                                ->get(),
            'emailLimit'  => $this->checkEmailLimit(),
            'queueStatus' => $this->QueueStatus()
        ];
    }

    protected function checkEmailLimit()
    {
        try {
            $subscription = $this->user->subscription;
            if (!$subscription || !$subscription->plan) {
                return ['show' => false];
            }

            $emailFeature = $subscription->plan->features()
                ->where('name', 'Subscribers Limit')
                ->first();

            if (!$emailFeature) {
                return ['show' => false];
            }

            $allowedEmails = (int)$emailFeature->pivot->charges;
            $currentEmails = $this->totalEmails; // In the Parent Component

            if ($currentEmails > $allowedEmails) {
                return [
                    'show'    => true,
                    'excess'  => $currentEmails - $allowedEmails,
                    'allowed' => $allowedEmails,
                    'current' => $currentEmails
                ];
            }

            return [
                'show'    => false,
                'allowed' => $allowedEmails,
                'current' => $currentEmails
            ];
        } catch (\Exception $e) {
            return [
                'show'    => false,
                'error'   => true,
                'message' => 'Unable to check email limit'
            ];
        }
    }

    public function QueueStatus()
    {
        $allJobs = DB::table('jobs')
            ->where('queue', 'high')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($allJobs as $index => $job) {
            // Check if this job belongs to the current user
            if (
                str_contains($job->payload, '"userId":' . $this->user->id) ||
                str_contains($job->payload, '"user_id":' . $this->user->id) ||
                str_contains($job->payload, 'i:' . $this->user->id . ';')
            ) {
                // Return position (1-based index)
                return $index + 1;
            }
        }

        return 0; // Return 0 if no jobs found for user
    }

    public function render()
    {
        return view('livewire.pages.user.emails.partials.job-progress-component', [
            'pollInterval' => $this->pollInterval
        ]);
    }
}
