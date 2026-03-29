<?php

namespace App\Jobs;

use App\Services\GeographicDataImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;


class ImportGeographicDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    /**
     * Create a new job instance.
     *
     * @param string $filePath .
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     * This method contains the actual import logic.
     *
     * @param GeographicDataImportService $importService 
     * @return void
     */
    public function handle(GeographicDataImportService $importService): void
    {
        try {
            $results = $importService->importFromExcel($this->filePath);

            Log::info('Geographic data import completed successfully.', ['results' => $results]);
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (Exception $e) {
            Log::error('Geographic data import failed: ' . $e->getMessage(), ['file_path' => $this->filePath, 'exception' => $e]);
            throw $e;
        }
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::critical('Geographic data import job permanently failed: ' . $exception->getMessage(), ['file_path' => $this->filePath]);
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }
}
