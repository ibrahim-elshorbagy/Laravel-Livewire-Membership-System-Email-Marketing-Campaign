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

class ProcessEmailFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 1;
    public $maxBatchSize = 1000;

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

    private function readFileInChunks($filePath): Generator
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Existing Excel file handling remains the same
            $reader = IOFactory::createReaderForFile(Storage::path($filePath));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(Storage::path($filePath));
            $worksheet = $spreadsheet->getActiveSheet();

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    $value = trim($cell->getValue());
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        yield $value;
                    }
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } else {
        $handle = fopen(Storage::path($filePath), 'r');
        $lineNumber = 0;
        $validEmailCount = 0;
        $invalidEmailCount = 0;
        $emailFormats = [
            'numbered' => 0,
            'plain' => 0
        ];

        if ($handle === false) {
            Log::error('Failed to open file', ['path' => $filePath]);
            return;
        }

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            // Modified preprocessing: Don't remove leading numbers if they're part of the email
            $line = trim($line);

            // First, check if there's an email with leading numbers
            if (preg_match('/^[\d\s*.-:)\-]*(\d+[^\s@]+@[^\s]+\.[^\s]+)$/i', $line, $numberedMatch)) {
                $potentialEmail = trim($numberedMatch[1]);
                // Process email with numbers intact
            } else {
                // Remove leading numbers only if they're not part of the email
                $line = preg_replace('/^[\d\s*.-:)\-]+/', '', $line);
                $line = trim($line);

                // Check for regular emails
                if (preg_match('/^[-\s]*([^\s]+@[^\s]+\.[^\s]+)$/i', $line, $matches)) {
                    $potentialEmail = trim($matches[1]);
                }
            }

            if (isset($potentialEmail) &&
                strpos($potentialEmail, '@') !== false &&
                strpos($potentialEmail, '.') !== false &&
                strlen($potentialEmail) >= 5) {

                // Determine email format
                $isNumbered = preg_match('/^\d+/', $potentialEmail);

                if ($isNumbered) {
                    $emailFormats['numbered']++;
                } else {
                    $emailFormats['plain']++;
                }

                // Log::info("Valid email found", [
                //     'line_number' => $lineNumber,
                //     'email' => $potentialEmail,
                //     'original_line' => $line,
                //     'format' => $isNumbered ? 'numbered' : 'plain'
                // ]);

                yield $potentialEmail;
                $validEmailCount++;
            }

            unset($potentialEmail); // Reset for next iteration
        }
    }

    }

    public function handle()
    {
        try {
            $processedCount = 0;
            $batch = [];
            $totalQuota = $this->remainingQuota;

            foreach ($this->readFileInChunks($this->filePath) as $email) {

                // Check if we've reached the quota before adding new email
                if ($processedCount >= $totalQuota) {
                    Log::info('Quota limit reached. Stopping processing.', [
                        'user_id' => $this->userId,
                        'processed' => $processedCount,
                        'quota' => $totalQuota
                    ]);
                    break;
                }

                $batch[] = [
                    'user_id' => $this->userId,
                    'email' => $email,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (count($batch) >= $this->maxBatchSize) {
                    // Check if this batch would exceed the quota
                    if ($processedCount + count($batch) > $totalQuota) {
                        // Trim the batch to fit within quota
                        $remainingSpace = $totalQuota - $processedCount;
                        $batch = array_slice($batch, 0, $remainingSpace);
                    }

                    $insertedCount = $this->insertBatch($batch);
                    $processedCount += $insertedCount;
                    $batch = [];

                    // Free up memory
                    gc_collect_cycles();

                    // Double-check quota after insertion
                    if ($processedCount >= $totalQuota) {
                        break;
                    }
                }
            }

            // Handle remaining batch
            if (!empty($batch)) {
                // Check if remaining batch would exceed quota
                if ($processedCount + count($batch) > $totalQuota) {
                    $remainingSpace = $totalQuota - $processedCount;
                    $batch = array_slice($batch, 0, $remainingSpace);
                }

                $insertedCount = $this->insertBatch($batch);
                $processedCount += $insertedCount;
            }

            // Update user's quota
            $user = \App\Models\User::find($this->userId);
            $totalEmailCount = EmailList::where('user_id', $this->userId)->count();
            $user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);

            // Clean up
            Storage::delete($this->filePath);

            // Log final results
            // Log::info('Email processing completed', [
            //     'user_id' => $this->userId,
            //     'processed' => $processedCount,
            //     'quota_limit' => $totalQuota
            // ]);

        } catch (\Exception $e) {
            Log::error('Email processing failed: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'processed_count' => $processedCount ?? 0,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function insertBatch(array &$batch): int
    {
        try {
            // Filter out existing emails
            $existingEmails = EmailList::whereIn('email', array_column($batch, 'email'))
                ->pluck('email')
                ->toArray();

            $newBatch = array_filter($batch, function($item) use ($existingEmails) {
                return !in_array($item['email'], $existingEmails);
            });

            if (!empty($newBatch)) {
                EmailList::insert($newBatch);
                return count($newBatch);
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Batch insertion failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch)
            ]);
            throw $e;
        }
    }
}
