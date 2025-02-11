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
use Generator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksProgress;

class ProcessEmailFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,TracksProgress;

    public $timeout = 7200; // 2 hours
    public $tries = 5; // Increased retry attempts
    public $maxBatchSize = 500; // Reduced batch size for better memory management
    public $backoff = [60, 120, 300, 600]; // Progressive retry delays

    protected $filePath;
    protected $userId;
    protected $remainingQuota;

    public function __construct($filePath, $userId, $remainingQuota)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->remainingQuota = $remainingQuota;
        $this->onQueue('high');

        JobProgress::where('user_id', $this->userId)
            ->where('job_type', 'process_email_file')
            ->where('status', 'processing')
            ->orWhere('status', 'failed')
            ->delete();

    }

    /**
     * Read the file in chunks.
     *
     * For Excel files we use PHPSpreadsheet’s read filter to load only a subset of rows.
     * For text files, we process them line by line.
     */
    private function readFileInChunks($filePath): Generator
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fullPath  = Storage::path($filePath);

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Use chunk reading to avoid high memory usage.
            $chunkSize = 5000;

            // Create a reader and set it to read data only.
            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true);

            // Get worksheet info (including total rows) without loading the full file.
            $sheetInfo = $reader->listWorksheetInfo($fullPath)[0];
            $totalRows = $sheetInfo['totalRows'];

            for ($startRow = 1; $startRow <= $totalRows; $startRow += $chunkSize) {
                // Create an anonymous read filter for the current chunk.
                $readFilter = new class($startRow, $startRow + $chunkSize - 1) implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                    private $startRow;
                    private $endRow;
                    public function __construct($startRow, $endRow)
                    {
                        $this->startRow = $startRow;
                        $this->endRow = $endRow;
                    }
                    public function readCell($column, $row, $worksheetName = ''): bool
                    {
                        return ($row >= $this->startRow && $row <= $this->endRow);
                    }
                };

                $reader->setReadFilter($readFilter);

                try {
                    $spreadsheet = $reader->load($fullPath);
                } catch (\Exception $e) {
                    Log::error('Failed to load spreadsheet chunk', [
                        'startRow' => $startRow,
                        'error'    => $e->getMessage()
                    ]);
                    continue; // Skip this chunk if an error occurs
                }

                $worksheet = $spreadsheet->getActiveSheet();

                // Iterate only over the rows in this chunk.
                foreach ($worksheet->getRowIterator($startRow, $startRow + $chunkSize - 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    foreach ($cellIterator as $cell) {
                        $value = trim((string)$cell->getValue());
                        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            yield $value;
                        }
                    }
                }

                // Free up memory by disconnecting worksheets and collecting garbage.
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                gc_collect_cycles();
            }
        } else {
            // For non-excel files, read line-by-line.
            $handle = fopen($fullPath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("Failed to open file: {$filePath}");
            }
            try {
                while (($line = fgets($handle)) !== false) {
                    $emails = $this->extractEmailsFromLine(trim($line));
                    foreach ($emails as $email) {
                        yield $email;
                    }
                }
            } finally {
                fclose($handle);
            }
        }
    }

    /**
     * Extract valid emails from a given line.
     */
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

    /**
     * Process the email file.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        try {
            // First check remaining quota - This doesn't need a transaction
            $currentCount = EmailList::where('user_id', $this->userId)->count();
            $remainingSpace = max(0, $this->remainingQuota - $currentCount);

            if ($remainingSpace <= 0) {
                $this->failProgress('User has exceeded quota');
                return;
            }

            // Get estimated total but cap it at remaining space
            $estimatedTotal = $this->getEstimatedTotal();
            $targetTotal = min($estimatedTotal, $remainingSpace);

            // Initialize progress with the capped total
            $this->initializeProgress('process_email_file', $this->userId, $targetTotal);

            $processedCount = 0;
            $batch = [];
            $totalInserted = 0;

            foreach ($this->readFileInChunks($this->filePath) as $email) {
                if ($processedCount >= $remainingSpace) {
                    break; // Stop if we hit the quota limit
                }

                $batch[] = [
                    'user_id' => $this->userId,
                    'email' => strtolower($email),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (count($batch) >= $this->maxBatchSize) {
                // Adjust batch size if it would exceed remaining quota
                
                if (count($batch) + $totalInserted > $remainingSpace) {
                    $batch = array_slice($batch, 0, $remainingSpace - $totalInserted);
                }
                DB::beginTransaction();
                    try {
                        $insertedCount = $this->insertBatch($batch);
                        $totalInserted += $insertedCount;
                        $processedCount += count($batch);

                        // Update quota for this batch
                        $this->updateQuota($currentCount + $totalInserted);

                        DB::commit();

                        // Update progress based on remaining space
                        $this->updateProgressWithQuota($processedCount, $targetTotal);

                        $batch = [];

                        if ($processedCount % 5000 === 0) {
                            gc_collect_cycles();
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Batch processing failed', [
                            'error' => $e->getMessage(),
                            'processed_count' => $processedCount
                        ]);
                        // Continue processing next batch instead of failing completely
                        $batch = [];
                        continue;
                    }
                }
            }

            // Process any remaining emails in the final batch
            if (!empty($batch)) {
                DB::beginTransaction();
                try {
                    $insertedCount = $this->insertBatch($batch);
                    $totalInserted += $insertedCount;
                    $processedCount += count($batch);

                    // Final quota update
                    $this->updateQuota($currentCount + $totalInserted);

                    DB::commit();

                    $this->updateProgressWithQuota($processedCount, $targetTotal);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Final batch processing failed', [
                        'error' => $e->getMessage(),
                        'processed_count' => $processedCount
                    ]);
                }
            }

            // Clean up and complete
            Storage::delete($this->filePath);

            // Set final progress
            $this->jobProgress->update([
                'total_items' => $processedCount,
                'processed_items' => $processedCount,
                'percentage' => 100,
                'status' => 'completed'
            ]);

            // Add completion message with quota information
            $this->completeProgressWithQuotaInfo($processedCount, $estimatedTotal, $remainingSpace);

        } catch (\Exception $e) {
            Log::error('Email processing failed', [
                'error' => $e->getMessage(),
                'processed_count' => $processedCount ?? 0
            ]);
            $this->failProgress($e->getMessage());
            throw $e;
        }
    }

    protected function insertBatch(array $batch): int
    {
        try {
            // Using insertOrIgnore to skip duplicates
            $inserted = DB::table('email_lists')->insertOrIgnore($batch);

            // Log successful batch insertion
            // Log::info('Batch inserted successfully', [
            //     'count' => $inserted,
            //     'batch_size' => count($batch)
            // ]);

            return $inserted;
        } catch (\Exception $e) {
            Log::error('Batch insert failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch)
            ]);
            throw $e;
        }
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
                Log::warning('Failed to read Excel file info, using size-based estimation', [
                    'error' => $e->getMessage()
                ]);
                return (int) ceil($fileSize / 100);
            }
        }

        // For text/CSV files
        return (int) ceil($fileSize / 30);
    }

    protected function updateProgressWithQuota(int $processedCount, int $targetTotal)
    {
        if ($this->jobProgress) {
            $this->jobProgress->update([
                'processed_items' => $processedCount,
                'total_items' => $targetTotal,
                'percentage' => min(99, ($processedCount / $targetTotal) * 100),
                'status' => 'processing'
            ]);
        }
    }

    protected function completeProgressWithQuotaInfo(int $processedCount, int $estimatedTotal, int $remainingSpace)
    {
        $message = "Processed {$processedCount} emails";

        if ($estimatedTotal > $remainingSpace) {
            $skippedCount = $estimatedTotal - $processedCount;
            $message .= ". {$skippedCount} emails were skipped due to quota limits";
        }

        $this->jobProgress->update([
            'status' => 'completed',
            'error' => $message // Using error field to store informational message
        ]);
    }


    /**
     * Update the user quota.
     */
    private function updateQuota(int $totalCount): void
    {
        try {
            $user = \App\Models\User::find($this->userId);
            $user->setConsumedQuota('Subscribers Limit', (float) $totalCount);

        } catch (\Exception $e) {
            Log::warning('Failed to update quota', [
                'error'   => $e->getMessage(),
                'user_id' => $this->userId,
                'count'   => $totalCount
            ]);
        }
    }
}
