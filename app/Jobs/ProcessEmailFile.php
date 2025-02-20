<?php

namespace App\Jobs;

use App\Models\EmailList;
use App\Models\JobProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksProgress;

class ProcessEmailFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksProgress;

    public $timeout = 7200;
    public $tries = 5;
    public $backoff = [60, 120, 300, 600];
    protected $filePath;
    protected $userId;
    protected $remainingQuota;
    protected $batchSize = 1000;

    protected $listId;

    public function __construct($filePath, $userId, $remainingQuota, $listId = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->remainingQuota = $remainingQuota;
        $this->listId = $listId;

        $this->onQueue('high');

        JobProgress::where('user_id', $this->userId)
            ->where('job_type', 'process_email_file')
            ->where('status', 'processing')
            ->orWhere('status', 'failed')
            ->delete();
    }

    public function handle()
    {
        ini_set('memory_limit', '512M');

        try {
            $currentCount = EmailList::where('user_id', $this->userId)->count();
            $remainingSpace = max(0, $this->remainingQuota - $currentCount);

            if ($remainingSpace <= 0) {
                $this->failProgress('User has exceeded quota');
                return;
            }

            $estimatedTotal = $this->getEstimatedTotal();
            $targetTotal = min($estimatedTotal, $remainingSpace);
            $this->initializeProgress('process_email_file', $this->userId, $targetTotal);

            $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

            if (in_array($extension, ['xlsx', 'xls'])) {
                $this->processExcelFile($remainingSpace, $targetTotal, $currentCount);
            } else {
                $this->processTextFile($remainingSpace, $targetTotal, $currentCount);
            }

            Storage::delete($this->filePath);
            $this->completeProgress();

        } catch (\Exception $e) {
            Log::error('Email processing failed', ['error' => $e->getMessage()]);
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }

    protected function processTextFile($remainingSpace, $targetTotal, $currentCount)
    {
        $fullPath = Storage::path($this->filePath);
        $handle = fopen($fullPath, 'r');
        $batch = [];
        $processedCount = 0;
        $now = now()->format('Y-m-d H:i:s');

        while (($line = fgets($handle)) !== false) {
            $emails = $this->extractEmailsFromLine(trim($line));

            foreach ($emails as $email) {


                if ($processedCount >= $remainingSpace) {
                    break 2;
                }


                $batch[] = [
                    'user_id' => $this->userId,
                    'list_id' => $this->listId,
                    'email' => $email,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                if (count($batch) >= $this->batchSize) {
                    $processedCount += $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $processedCount += $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
        }

        fclose($handle);
    }

    protected function processExcelFile($remainingSpace, $targetTotal, $currentCount)
    {
        $fullPath = Storage::path($this->filePath);
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($fullPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $batch = [];
        $processedCount = 0;
        $now = now()->format('Y-m-d H:i:s');

        foreach ($worksheet->getRowIterator() as $row) {
            if ($processedCount >= $remainingSpace) {
                break;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $value = trim((string)$cell->getValue());
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {

                    if ($processedCount >= $remainingSpace) {
                        break 2;
                    }


                    $batch[] = [
                        'user_id' => $this->userId,
                        'list_id' => $this->listId,
                        'email' => $value,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];

                    if (count($batch) >= $this->batchSize) {
                        $processedCount += $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
                        $batch = [];
                    }
                }
            }
        }

        if (!empty($batch)) {
            $processedCount += $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    protected function insertBatchAndUpdateProgress(array $batch, $remainingSpace, $currentCount, $processedCount)
    {
        if (empty($batch)) {
            return 0;
        }

        if ($processedCount >= $remainingSpace) {
            return 0;
        }

        if (count($batch) + $processedCount > $remainingSpace) {
            $batch = array_slice($batch, 0, $remainingSpace - $processedCount);
        }

        DB::beginTransaction();
        try {
            $inserted = DB::table('email_lists')->insertOrIgnore($batch);

            if ($inserted > 0) {
                $this->updateQuota($currentCount + $processedCount + $inserted);
                $this->updateProgress($processedCount + $inserted);
            }

            DB::commit();
            return $inserted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch insert failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function extractEmailsFromLine(string $line): array
    {
        $emails = [];
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $line, $matches);
        foreach ($matches[0] as $potentialEmail) {
            if (filter_var($potentialEmail, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $potentialEmail;
            }
        }
        return $emails;
    }

    protected function getEstimatedTotal(): int
    {
        $fileSize = Storage::size($this->filePath);
        $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'])) {
            try {
                $reader = IOFactory::createReaderForFile(Storage::path($this->filePath));
                $reader->setReadDataOnly(true);
                $worksheetInfo = $reader->listWorksheetInfo(Storage::path($this->filePath));
                return $worksheetInfo[0]['totalRows'];
            } catch (\Exception $e) {
                return (int) ceil($fileSize / 100);
            }
        }
        return (int) ceil($fileSize / 30);
    }

    private function updateQuota(int $totalCount): void
    {
        try {
            $user = \App\Models\User::find($this->userId);
            $user->setConsumedQuota('Subscribers Limit', (float) $totalCount);
        } catch (\Exception $e) {
            Log::warning('Failed to update quota', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'count' => $totalCount
            ]);
        }
    }
}
