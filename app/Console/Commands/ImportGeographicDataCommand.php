<?php

namespace App\Console\Commands;

use App\Services\GeographicDataImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImportGeographicDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:geographic-data {file : The path to the Excel/CSV file to import (e.g., storage/app/imports/data.xlsx)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports geographic data from a specified Excel/CSV file into the database.';

    /**
     * The geographic data import service instance.
     *
     * @var GeographicDataImportService
     */
    protected GeographicDataImportService $importService;

    /**
     * Create a new command instance.
     *
     * @param GeographicDataImportService $importService
     * @return void
     */
    public function __construct(GeographicDataImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        // Check if the file exists in storage or as an absolute path
        if (!Storage::exists($filePath) && !file_exists($filePath)) {
            $this->error("Error: The specified file does not exist at '{$filePath}'.");
            return Command::FAILURE;
        }

        // If the file is in storage, get its absolute path
        if (Storage::exists($filePath)) {
            $absoluteFilePath = Storage::path($filePath);
        } else {
            // Assume it's an absolute path provided directly
            $absoluteFilePath = $filePath;
        }

        $this->info("Starting geographic data import from: {$absoluteFilePath}");

        try {
            $results = $this->importService->importFromExcel($absoluteFilePath);

            $this->info('Importation des données géographiques terminée avec succès.');
            $this->table(
                ['Category', 'Count'],
                [
                    ['Regions Created', $results['regions_created']],
                    ['Provinces Created', $results['provinces_created']],
                    ['Cercles Created', $results['cercles_created']],
                    ['Communes Created', $results['communes_created']],
                    ['Douars Created', $results['douars_created']],
                    ['Errors Encountered', $results['errors']],
                ]
            );

            if (!empty($results['warnings'])) {
                $this->warn("\nWarnings during import:");
                foreach ($results['warnings'] as $warning) {
                    $this->warn("- {$warning}");
                }
            }

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("Error during geographic data import: " . $e->getMessage());
            $this->error("Please check your file format and content, and ensure all parent entities exist or are included in the import.");
            return Command::FAILURE;
        }
    }
}
