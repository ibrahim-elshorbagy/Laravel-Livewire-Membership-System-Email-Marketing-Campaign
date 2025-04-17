<?php
// app/Traits/TracksProgress.php

namespace App\Traits;

use App\Models\JobProgress;

trait TracksProgress
{
    protected $jobProgress;

    protected function initializeProgress($jobType, $userId, $totalItems)
    {
        $jobId = property_exists($this, 'job') ? $this->job->getJobId() : $this->jobId;

        $this->jobProgress = JobProgress::create([
            'job_id' => $jobId,
            'job_type' => $jobType,
            'user_id' => $userId,
            'total_items' => $totalItems,
            'processed_items' => 0,
            'status' => 'processing',
            'percentage' => 0
        ]);
    }


    protected function updateProgress($processedItems)
    {
        if ($this->jobProgress) {
            $this->jobProgress->updateProgress($processedItems);
        }
    }

    protected function updateProgressTotal($totalItems)
    {
        if ($this->jobProgress) {
            $this->jobProgress->updateProgress($this->jobProgress->processed_items, $totalItems);
        }
    }

    public function completeProgress()
    {
        if ($this->jobProgress) {
            $this->jobProgress->update([
                'status' => 'completed',
                'percentage' => 100
            ]);
        }
    }

    public function failProgress($error)
    {
        if ($this->jobProgress) {
            $this->jobProgress->update([
                'status' => 'failed',
                'error' => $error
            ]);
        }
    }
}
