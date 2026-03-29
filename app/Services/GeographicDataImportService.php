<?php

namespace App\Services;

use App\Imports\GeographicImport; 
use App\Models\Region;
use App\Models\Province;
use App\Models\Cercle;
use App\Models\Commune;
use App\Models\Douar;
use App\Models\Country; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class GeographicDataImportService
{
    /**
     *
     * @param string $filePath The full path to the uploaded file.
     * @return array<string, int|array<string>>
     */
    public function importFromExcel(string $filePath): array
    {
        set_time_limit(0); 

        $results = [
            'regions_created' => 0,
            'provinces_created' => 0,
            'cercles_created' => 0,
            'communes_created' => 0,
            'douars_created' => 0,
            'errors' => 0,
            'warnings' => [],
        ];

        $morocco = Country::firstOrCreate(
            ['code' => 'MA'],
            ['name' => 'Morocco']
        );
        $defaultCountryId = $morocco->id;
        Log::info("Default country 'Morocco' (ID: {$defaultCountryId}) ensured for import.");
        
        $regionsMap = Region::all()->mapWithKeys(function ($region) {
            return [strtolower($region->code . '_' . $region->country_id) => $region->id];
        })->all();
        
        $provincesMap = Province::all()->mapWithKeys(function ($province) {
            return [strtolower($province->code . '_' . $province->region_id) => $province->id];
        })->all();

        $cerclesMap = Cercle::all()->mapWithKeys(function ($cercle) {
            return [strtolower($cercle->code . '_' . $cercle->province_id) => $cercle->id];
        })->all();

        $communesMap = Commune::all()->mapWithKeys(function ($commune) {
            return [strtolower($commune->code . '_' . $commune->province_id) => $commune->id];
        })->all();

        $douarsMap = Douar::all()->mapWithKeys(function ($douar) {
            return [strtolower($douar->code . '_' . $douar->commune_id) => $douar->id];
        })->all();


        try {
            DB::beginTransaction();
            Excel::import(
                new GeographicImport(
                    $results,
                    $regionsMap,
                    $provincesMap,
                    $cerclesMap,
                    $communesMap,
                    $douarsMap,
                    $defaultCountryId 
                ),
                $filePath
            );

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error during geographic data import: " . $e->getMessage(), ['file_path' => $filePath, 'exception' => $e]);
            throw new Exception("Importation échouée: " . $e->getMessage());
        }

        return $results;
    }
}
