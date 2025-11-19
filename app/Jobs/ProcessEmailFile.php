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
    protected $listId;
    protected $batchSize = 1000;

    public function __construct($filePath, $userId, $remainingQuota, $listId = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->remainingQuota = $remainingQuota;
        $this->listId = $listId;

        $this->onQueue('high');

        JobProgress::where('user_id', $this->userId)
            ->where('job_type', 'process_email_file')
            ->whereIn('status', ['processing', 'failed'])
            ->delete();
    }

    public function handle()
    {
        try {
            $remainingSpace = $this->remainingQuota;

            if ($remainingSpace <= 0) {
                $this->failProgress('User has exceeded quota');
                return;
            }

            $estimatedTotal = $this->getEstimatedTotal();
            $targetTotal = min($estimatedTotal, $remainingSpace);

            $this->initializeProgress('process_email_file', $this->userId, $targetTotal);

            $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

            if (in_array($extension, ['xlsx', 'xls'])) {
                $this->processExcelFile($remainingSpace);
            } elseif ($extension === 'csv') {
                $this->processCsvFile($remainingSpace);
            } else {
                $this->processTextFile($remainingSpace);
            }

            Storage::delete($this->filePath);
            $this->completeProgress();
        } catch (\Exception $e) {
            Log::error('Email processing failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }

    protected function processExcelFile($remainingSpace)
    {
        $fullPath = Storage::path($this->filePath);
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($fullPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $batch = [];
        $processedCount = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            if ($processedCount >= $remainingSpace) {
                break;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $value = trim((string)$cell->getValue());
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $rowData['email'] = $value;
                } else {
                    if (is_string($value) && strlen($value) <= 255 && !preg_match('/[\x00-\x1F\x7F]/', $value)) {
                        $rowData['name'] = strip_tags(trim($value));
                    }
                }
            }

            if (isset($rowData['email'])) {
                $batch[] = [
                    'user_id' => $this->userId,
                    'list_id' => $this->listId,
                    'email' => $rowData['email'],
                    'name' => $rowData['name'] ?? null,
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->upsertBatch($batch);
                    $processedCount += count($batch);
                    $this->updateProgress($processedCount);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $processedCount += count($batch);
            $this->updateProgress($processedCount);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }


    protected function processCsvFile($remainingSpace)
    {
        $fullPath = Storage::path($this->filePath);

        // Convert encoding if needed
        $tempPath = $fullPath . '_utf8.csv';
        $in = fopen($fullPath, 'r');
        $out = fopen($tempPath, 'w');

        while (($line = fgets($in)) !== false) {
            $utf8Line = iconv('CP1256', 'UTF-8//IGNORE', $line);
            fwrite($out, $utf8Line);
        }

        fclose($in);
        fclose($out);

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setInputEncoding('UTF-8');
        $reader->setEnclosure('"');
        $reader->setEscapeCharacter('\\');

        $spreadsheet = $reader->load($tempPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $batch = [];
        $processedCount = 0;
        $highestRow = $worksheet->getHighestRow();
        $startRow = 2; // Skip header

        for ($row = $startRow; $row <= $highestRow; $row++) {
            if ($processedCount >= $remainingSpace) {
                break;
            }

            $emailValue = $worksheet->getCell('A' . $row)->getValue();
            $nameValue = $worksheet->getCell('B' . $row)->getValue();

            // Handle comma-separated format
            if (strpos($emailValue, ',') !== false) {
                $parts = explode(',', $emailValue, 2);
                $emailValue = trim($parts[0], '"');
                if (empty($nameValue) && isset($parts[1])) {
                    $nameValue = trim($parts[1], '"');
                }
            }

            if (filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                $batch[] = [
                    'user_id' => $this->userId,
                    'list_id' => $this->listId,
                    'email' => $emailValue,
                    'name' => $nameValue && strlen($nameValue) <= 255 ? strip_tags(trim($nameValue)) : null,
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->upsertBatch($batch);
                    $processedCount += count($batch);
                    $this->updateProgress($processedCount);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $processedCount += count($batch);
            $this->updateProgress($processedCount);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Clean up temp file
        @unlink($tempPath);
    }


    protected function processTextFile($remainingSpace)
    {
        $fullPath = Storage::path($this->filePath);
        $handle = fopen($fullPath, 'r');
        $batch = [];
        $processedCount = 0;

        while (($line = fgets($handle)) !== false && $processedCount < $remainingSpace) {
            $emails = $this->extractEmailsFromLine(trim($line));

            foreach ($emails as $email) {
                if ($processedCount >= $remainingSpace) {
                    break 2;
                }

                $batch[] = [
                    'user_id' => $this->userId,
                    'list_id' => $this->listId,
                    'email' => $email,
                    'name' => null,
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->upsertBatch($batch);
                    $processedCount += count($batch);
                    $this->updateProgress($processedCount);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $processedCount += count($batch);
            $this->updateProgress($processedCount);
        }

        fclose($handle);
    }

    /**
     * Insert or update batch with upsert
     * If email exists for user+list, update name
     * If email doesn't exist, insert new record
     */
    protected function upsertBatch(array $batch)
    {
        if (empty($batch)) {
            return;
        }

        try {
            // Remove timestamps from batch data
            $cleanBatch = array_map(function ($item) {
                return [
                    'user_id' => $item['user_id'],
                    'list_id' => $item['list_id'],
                    'email' => $item['email'],
                    'name' => $item['name'],
                ];
            }, $batch);

            // Upsert: update name if exists, insert if new
            EmailList::upsert(
                $cleanBatch,
                ['user_id', 'list_id', 'email'], // Unique columns combination
                ['name'] // Only update name if exists
            );

            // Update quota after successful insert
            $this->updateQuota();
        } catch (\Exception $e) {
            Log::error('Batch upsert failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId
            ]);
        }
    }


    protected function updateQuota(): void
    {
        try {
            $totalCount = EmailList::where('user_id', $this->userId)->count();
            $user = \App\Models\User::find($this->userId);
            $subscribersLimitName = \LucasDotVin\Soulbscription\Models\Feature::find(1)?->name;

            if ($user && $subscribersLimitName) {
                $user->setConsumedQuota($subscribersLimitName, (float) $totalCount);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update quota', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
            ]);
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

        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            try {
                $reader = IOFactory::createReaderForFile(Storage::path($this->filePath));
                $reader->setReadDataOnly(true);
                $worksheetInfo = $reader->listWorksheetInfo(Storage::path($this->filePath));
                return $worksheetInfo[0]['totalRows'] ?? (int) ceil($fileSize / 100);
            } catch (\Exception $e) {
                Log::warning('Failed to get worksheet info', ['error' => $e->getMessage()]);
                return (int) ceil($fileSize / 100);
            }
        }
        return (int) ceil($fileSize / 30);
    }
}
