<?php

namespace App\Jobs;

use App\Models\EmailList;
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

class ProcessEmailFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        // Increase memory limit if needed (adjust as necessary).
        ini_set('memory_limit', '512M');

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $batch = [];

            // Get current email count for the user.
            $currentCount   = EmailList::where('user_id', $this->userId)->count();
            $remainingSpace = max(0, $this->remainingQuota - $currentCount);

            if ($remainingSpace <= 0) {
                Log::warning('User has already exceeded quota', [
                    'user_id'       => $this->userId,
                    'current_count' => $currentCount,
                    'quota'         => $this->remainingQuota
                ]);
                return;
            }

            // Process each email yielded by the chunk reader.
            foreach ($this->readFileInChunks($this->filePath) as $email) {
                // Stop if the quota is reached.
                if ($processedCount >= $remainingSpace) {
                    break;
                }

                $batch[] = [
                    'user_id'    => $this->userId,
                    'email'      => strtolower($email),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Insert in batches to minimize DB load.
                if (count($batch) >= $this->maxBatchSize) {
                    $insertedCount = $this->insertBatch($batch);
                    $processedCount += $insertedCount;
                    $batch = [];

                    // Update quota and force garbage collection every 5000 processed emails.
                    if ($processedCount % 5000 === 0) {
                        $this->updateQuota($currentCount + $processedCount);
                        gc_collect_cycles();
                    }
                }
            }

            // Insert any remaining emails.
            if (!empty($batch)) {
                $insertedCount = $this->insertBatch($batch);
                $processedCount += $insertedCount;
            }

            // Final quota update.
            $this->updateQuota($currentCount + $processedCount);

            DB::commit();
            Storage::delete($this->filePath);

            Log::info('Email processing completed', [
                'user_id'     => $this->userId,
                'processed'   => $processedCount,
                'total_count' => $currentCount + $processedCount,
                'quota_limit' => $this->remainingQuota
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email processing failed', [
                'user_id' => $this->userId,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ]);

            if ($this->attempts() >= $this->tries) {
                throw $e;
            }

            // Release back to queue with a delay.
            $this->release(60);
        }
    }

    /**
     * Insert a batch of emails into the database.
     */
    private function insertBatch(array $batch): int
    {
        try {
            // Check if adding this batch would exceed the user’s quota.
            $currentCount = EmailList::where('user_id', $this->userId)->count();
            $batchSize    = count($batch);

            if ($currentCount + $batchSize > $this->remainingQuota) {
                $remainingSpace = max(0, $this->remainingQuota - $currentCount);
                if ($remainingSpace <= 0) {
                    return 0;
                }
                $batch = array_slice($batch, 0, $remainingSpace);
            }

            // Using insertOrIgnore to avoid duplicate entries.
            DB::table('email_lists')->insertOrIgnore($batch);
            return count($batch);
        } catch (\Exception $e) {
            Log::error('Batch insert failed', ['error' => $e->getMessage()]);
            throw $e;
        }
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
