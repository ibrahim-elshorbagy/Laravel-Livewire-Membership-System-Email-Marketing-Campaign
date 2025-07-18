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
use LucasDotVin\Soulbscription\Models\Feature;

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

    JobProgress::where('user_id', $this->userId)   //As if there is Error happend and it does not complete so redo it
      ->where('job_type', 'process_email_file')
      ->where('status', 'processing')
      ->orWhere('status', 'failed')
      ->delete();
  }

  public function handle()
  {
    try {
      $currentCount = EmailList::where('user_id', $this->userId)->count();
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
        $this->processExcelFile($remainingSpace, $targetTotal, $currentCount);
      } elseif ($extension === 'csv') {
        $this->processCsvFile($remainingSpace, $targetTotal, $currentCount);
      } else {
        $this->processTextFile($remainingSpace, $targetTotal, $currentCount);
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


  protected function processTextFile($remainingSpace, $targetTotal, $currentCount)
  {
    $fullPath = Storage::path($this->filePath);
    $handle = fopen($fullPath, 'r');
    $batch = [];
    $processedCount = 0;

    while (($line = fgets($handle)) != false) {
      $emails = $this->extractEmailsFromLine(trim($line));

      foreach ($emails as $email) {


        if ($processedCount >= $remainingSpace) {
          break 2;
        }


        $batch[] = [
          'user_id' => $this->userId,
          'list_id' => $this->listId,
          'email' => $email,
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
          // Validate and sanitize name string
          if (is_string($value) && strlen($value) <= 255 && !preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $rowData['name'] = strip_tags(trim($value));
          }
        }
      }

      if (isset($rowData['email'])) {
        if ($processedCount >= $remainingSpace) {
          break;
        }

        $batch[] = [
          'user_id' => $this->userId,
          'list_id' => $this->listId,
          'email' => $rowData['email'],
          'name' => $rowData['name'] ?? null,
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

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
  }

  protected function insertBatchAndUpdateProgress(array $batch, $remainingSpace, $currentCount, $processedCount)
  {
    Log::info('Attempting to insert batch', [
      'batch_size' => count($batch),
      'processed_count' => $processedCount,
      'remaining_space' => $remainingSpace,
    ]);

    if (empty($batch) || $processedCount >= $remainingSpace) {
      Log::warning('Skipping batch insert due to quota or empty batch', [
        'processed_count' => $processedCount,
        'remaining_space' => $remainingSpace,
      ]);
      return 0;
    }

    if (count($batch) + $processedCount > $remainingSpace) {
      $batch = array_slice($batch, 0, $remainingSpace - $processedCount);
      Log::info('Trimmed batch to fit remaining quota', ['new_batch_size' => count($batch)]);
    }

    DB::beginTransaction();
    try {
      $inserted = DB::table('email_lists')->insertOrIgnore($batch);
      Log::info('Batch insertOrIgnore result', ['inserted' => $inserted]);

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

    if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
      try {
        $reader = IOFactory::createReaderForFile(Storage::path($this->filePath));
        $reader->setReadDataOnly(true);
        $worksheetInfo = $reader->listWorksheetInfo(Storage::path($this->filePath));
        return $worksheetInfo[0]['totalRows'];
      } catch (\Exception $e) {
        Log::warning('Failed to get worksheet info', ['error' => $e->getMessage()]);
        return (int) ceil($fileSize / 100);
      }
    }
    return (int) ceil($fileSize / 30);
  }

  protected function processCsvFile($remainingSpace, $targetTotal, $currentCount)
  {
    $fullPath = Storage::path($this->filePath);
    // Log::info('Processing CSV file', ['file' => $this->filePath, 'fullPath' => $fullPath]);

    try {

      $tempPath = $fullPath . '_utf8.csv';
      $in = fopen($fullPath, 'r');
      $out = fopen($tempPath, 'w');

      while (($line = fgets($in)) !== false) {
        $utf8Line = iconv('CP1256', 'UTF-8//IGNORE', $line);
        fwrite($out, $utf8Line);
      }

      fclose($in);
      fclose($out);

      // Now use $tempPath for PhpSpreadsheet
      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      $reader->setInputEncoding('UTF-8');
      $reader->setEnclosure('"');
      $reader->setEscapeCharacter('\\');

      $spreadsheet = $reader->load($tempPath);

      // Load the CSV file
      $worksheet = $spreadsheet->getActiveSheet();

      // Log::info('CSV file loaded successfully', ['worksheet' => $worksheet->getTitle()]);

      $batch = [];
      $processedCount = 0;
      $rowCount = 0;

      // Get the highest row and column indexes
      $highestRow = $worksheet->getHighestRow();
      $highestColumn = $worksheet->getHighestColumn();

      // Log::info('CSV dimensions', ['rows' => $highestRow, 'columns' => $highestColumn]);

      // Skip header row if exists
      $startRow = 2; // Assuming first row is header

      // Iterate through rows
      for ($row = $startRow; $row <= $highestRow; $row++) {
        if ($processedCount >= $remainingSpace) {
          // Log::info('Reached quota limit', ['processed' => $processedCount, 'quota' => $remainingSpace]);
          break;
        }

        $rowData = [];

        // Get email from column A
        $emailValue = $worksheet->getCell('A' . $row)->getValue();
        // Get name from column B if exists
        $nameValue = $worksheet->getCell('B' . $row)->getValue();

        // Clean and validate email
        if ($emailValue) {
          // Extract email if it's in a format like "email@example.com,"Name""
          if (strpos($emailValue, ',') !== false) {
            $parts = explode(',', $emailValue, 2);
            $emailValue = trim($parts[0], '"');
            // If name wasn't in column B, use the second part of the split
            if (empty($nameValue) && isset($parts[1])) {
              $nameValue = trim($parts[1], '"');
            }
          }

          // Validate email
          if (filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $rowData['email'] = $emailValue;

            // Validate and sanitize name
            if ($nameValue && is_string($nameValue) && strlen($nameValue) <= 255) {
              $rowData['name'] = strip_tags(trim($nameValue));
            }

            // Log::debug('Extracted data', ['row' => $row, 'email' => $rowData['email'], 'name' => $rowData['name'] ?? 'null']);

            $batch[] = [
              'user_id' => $this->userId,
              'list_id' => $this->listId,
              'email' => $rowData['email'],
              'name' => $rowData['name'] ?? null,
            ];

            if (count($batch) >= $this->batchSize) {
              $inserted = $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
              $processedCount += $inserted;
              // Log::info('Batch inserted', ['count' => $inserted, 'total_processed' => $processedCount]);
              $batch = [];
            }
          } else {
            Log::warning('Invalid email found', ['row' => $row, 'value' => $emailValue]);
          }
        }

        $rowCount++;
      }

      // Insert any remaining records
      if (!empty($batch)) {
        $inserted = $this->insertBatchAndUpdateProgress($batch, $remainingSpace, $currentCount, $processedCount);
        $processedCount += $inserted;
        // Log::info('Final batch inserted', ['count' => $inserted, 'total_processed' => $processedCount]);
      }

      // Log::info('CSV processing completed', ['total_rows' => $rowCount, 'processed' => $processedCount]);

      // Clean up
      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);

      return $processedCount;
    } catch (\Exception $e) {
      Log::error('CSV processing failed', [
        'error' => $e->getMessage(),
        'file' => $this->filePath,
        'trace' => $e->getTraceAsString()
      ]);
      throw $e;
    }
  }

  private function updateQuota(int $totalCount): void
  {
    try {


      $user = \App\Models\User::find($this->userId);
      $subscribersLimitName = Feature::find(1)?->name;

      $user->setConsumedQuota($subscribersLimitName, (float) $totalCount);
    } catch (\Exception $e) {
      Log::warning('Failed to update quota', [
        'error' => $e->getMessage(),
        'user_id' => $this->userId,
        'count' => $totalCount
      ]);
    }
  }
}
