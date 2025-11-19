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
use PhpOffice\PhpSpreadsheet\Reader\Csv;
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

        // Step 1: Clean the CSV file
        $cleanedPath = $this->cleanCsvFile($fullPath);

        // Step 2: Detect delimiter
        $delimiter = $this->detectCsvDelimiter($cleanedPath);

        Log::info('CSV processing info', [
            'delimiter' => $delimiter,
            'file' => $this->filePath
        ]);

        // Step 3: Create CSV reader with detected delimiter
        $reader = new Csv();
        $reader->setDelimiter($delimiter);
        $reader->setEnclosure('"');
        $reader->setEscapeCharacter('\\');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        // Try to detect encoding
        try {
            $encoding = Csv::guessEncoding($cleanedPath, 'CP1256');
            $reader->setInputEncoding($encoding);
        } catch (\Exception $e) {
            $reader->setInputEncoding('UTF-8');
        }

        // Step 4: Load the cleaned spreadsheet
        $spreadsheet = $reader->load($cleanedPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $batch = [];
        $processedCount = 0;
        $skippedCount = 0;
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        Log::info('CSV file loaded', [
            'highest_row' => $highestRow,
            'highest_column' => $highestColumn,
            'file' => $this->filePath
        ]);

        // Start from row 2 to skip header
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($processedCount >= $remainingSpace) {
                break;
            }

            // Get values from columns A and B
            $emailValue = $worksheet->getCell('A' . $row)->getValue();
            $nameValue = $worksheet->getCell('B' . $row)->getValue();

            // Skip empty rows
            if (empty($emailValue)) {
                $skippedCount++;
                continue;
            }

            // Clean email value
            $emailValue = trim((string)$emailValue);

            // NEW: Handle mixed delimiter case - if email contains comma or semicolon, split it
            if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                // Check if it contains a delimiter (comma or semicolon) and no name was found
                if (empty($nameValue) && (strpos($emailValue, ',') !== false || strpos($emailValue, ';') !== false)) {
                    // Try to split by comma first, then semicolon
                    $parts = preg_split('/[,;]/', $emailValue, 2);
                    if (count($parts) === 2) {
                        $emailValue = trim($parts[0]);
                        $nameValue = trim($parts[1]);

                        Log::info('Split mixed delimiter row', [
                            'row' => $row,
                            'email' => $emailValue,
                            'name' => $nameValue
                        ]);
                    }
                }
            }

            // Validate email after potential splitting
            if (filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                // Clean and validate name
                $cleanName = null;
                if ($nameValue) {
                    $nameValue = trim((string)$nameValue);
                    // Remove trailing quotes if present (from your log: "كلام جديد.\"\"")
                    $nameValue = rtrim($nameValue, '"');
                    if (strlen($nameValue) <= 255 && !preg_match('/[\x00-\x1F\x7F]/', $nameValue)) {
                        $cleanName = strip_tags($nameValue);
                    }
                }

                $batch[] = [
                    'user_id' => $this->userId,
                    'list_id' => $this->listId,
                    'email' => $emailValue,
                    'name' => $cleanName,
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->upsertBatch($batch);
                    $processedCount += count($batch);
                    $this->updateProgress($processedCount);
                    $batch = [];
                }
            } else {
                $skippedCount++;
                Log::warning('Invalid email after processing', [
                    'row' => $row,
                    'email' => $emailValue
                ]);
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $processedCount += count($batch);
            $this->updateProgress($processedCount);
        }

        Log::info('CSV processing completed', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total_rows' => $highestRow - 1
        ]);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Clean up temp file
        @unlink($cleanedPath);
    }


    /**
     * Clean CSV file by removing outer quotes that wrap entire rows
     * Supports both comma (,) and semicolon (;) delimiters
     */
    protected function cleanCsvFile(string $filePath): string
    {
        $cleanedPath = $filePath . '_cleaned.csv';
        $input = fopen($filePath, 'r');
        $output = fopen($cleanedPath, 'w');

        while (($line = fgets($input)) !== false) {
            // Remove BOM if present
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);

            // Trim whitespace
            $line = trim($line);

            // If line starts and ends with quotes, and has delimiters inside
            // Remove the outer quotes so PhpSpreadsheet can parse properly
            if (preg_match('/^"(.+)"$/', $line, $matches)) {
                $inner = $matches[1];
                // Check if there are semicolons OR commas inside (our actual delimiters)
                if (strpos($inner, ';') !== false || strpos($inner, ',') !== false) {
                    // Replace escaped inner quotes \" with just "
                    $inner = str_replace('\\"', '"', $inner);
                    $line = $inner;
                }
            }

            fwrite($output, $line . "\n");
        }

        fclose($input);
        fclose($output);

        return $cleanedPath;
    }

    /**
     * Auto-detect CSV delimiter (comma, semicolon, tab, or pipe)
     */
    protected function detectCsvDelimiter(string $filePath): string
    {
        $delimiters = [
            ',' => 0,
            ';' => 0,
            "\t" => 0,
            '|' => 0,
        ];

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ','; // Default to comma
        }

        $firstLine = fgets($handle);
        fclose($handle);

        if (!$firstLine) {
            return ',';
        }

        // Count occurrences of each delimiter
        foreach ($delimiters as $delimiter => $count) {
            $columns = str_getcsv($firstLine, $delimiter);
            if (count($columns) > 1) {
                $delimiters[$delimiter] = count($columns);
            } else {
                unset($delimiters[$delimiter]);
            }
        }

        // Return delimiter with most columns
        if (!empty($delimiters)) {
            return array_search(max($delimiters), $delimiters);
        }

        return ','; // Default to comma
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
            $cleanBatch = array_map(function ($item) {
                return [
                    'user_id' => $item['user_id'],
                    'list_id' => $item['list_id'],
                    'email' => $item['email'],
                    'name' => $item['name'],
                ];
            }, $batch);

            EmailList::upsert(
                $cleanBatch,
                ['user_id', 'list_id', 'email'],
                ['name']
            );

            $this->updateQuota();
        } catch (\Exception $e) {
            Log::error('Batch upsert failed', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'batch_sample' => array_slice($batch, 0, 2)
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
