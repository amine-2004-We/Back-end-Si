<?php

namespace App\Imports;

use App\Models\Cercle;
use App\Models\Commune;
use App\Models\Douar;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GeographicImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected array $results;
    protected array $regionsMap;
    protected array $provincesMap;
    protected array $cerclesMap;
    protected array $communesMap;
    protected array $douarsMap;
    protected int $defaultCountryId; 

    public function __construct(
        array &$results,
        array &$regionsMap,
        array &$provincesMap,
        array &$cerclesMap,
        array &$communesMap,
        array &$douarsMap,
        int $defaultCountryId 
    ) {
        $this->results = &$results;
        $this->regionsMap = &$regionsMap;
        $this->provincesMap = &$provincesMap;
        $this->cerclesMap = &$cerclesMap;
        $this->communesMap = &$communesMap;
        $this->douarsMap = &$douarsMap;
        $this->defaultCountryId = $defaultCountryId;
    }

    public function collection(Collection $rows)
    {
        Log::info("Processing a chunk of " . $rows->count() . " rows.");

        $newRegions = [];
        $newProvinces = [];
        $newCercles = [];
        $newCommunes = [];
        $newDouars = [];

        foreach ($rows as $row) {
            $codeR = trim($row['code_region_ref'] ?? '');
            $nameR = trim($row['region_ref'] ?? '');
            $codeP = trim($row['code_province_ref'] ?? '');
            $nameP = trim($row['province_ref'] ?? '');
            $codeC = trim($row['code_cercle_ref'] ?? '');
            $nameC = trim($row['cercle_ref'] ?? '');
            $codeCm = trim($row['code_commune_ref'] ?? '');
            $nameCm = trim($row['commune_ref'] ?? '');
            $codeD = trim($row['code_douar_ref'] ?? '');
            $nameD = trim($row['douar_ref'] ?? '');

            if (!empty($codeR) && !empty($nameR)) {
                $regionKey = strtolower($codeR . '_' . $this->defaultCountryId);
                if (!isset($this->regionsMap[$regionKey])) { 
                    $newRegions[$regionKey] = [
                        'code' => $codeR,
                        'name' => $nameR,
                        'country_id' => $this->defaultCountryId 
                    ];
                }
            } else {
                Log::warning("Skipping row due to missing Region Code or Name: " . json_encode($row));
                $this->results['warnings'][] = "Skipping row due to missing Region Code or Name.";
                continue;
            }

            if (!empty($codeP) && !empty($nameP)) {
                $provinceKey = strtolower($codeP . '_' . $codeR . '_' . $this->defaultCountryId); 
                if (!isset($this->provincesMap[$provinceKey])) {
                    $newProvinces[$provinceKey] = [
                        'code' => $codeP,
                        'name' => $nameP,
                        'region_code_temp' => $codeR, 
                        'country_id_temp' => $this->defaultCountryId 
                    ];
                }
            } else {
                Log::warning("Skipping row due to missing Province Code or Name: " . json_encode($row));
                $this->results['warnings'][] = "Skipping row due to missing Province Code or Name.";
                continue;
            }

            if (!empty($codeC) && !empty($nameC)) {
                $cercleKey = strtolower($codeC . '_' . $codeP . '_' . $codeR . '_' . $this->defaultCountryId); // Include region and country for full context
                if (!isset($this->cerclesMap[$cercleKey])) {
                    $newCercles[$cercleKey] = [
                        'code' => $codeC,
                        'name' => $nameC,
                        'province_code_temp' => $codeP,
                        'region_code_temp' => $codeR, 
                        'country_id_temp' => $this->defaultCountryId 
                    ];
                }
            }

            if (!empty($codeCm) && !empty($nameCm)) {
                $communeKey = strtolower($codeCm . '_' . $codeP . '_' . $codeR . '_' . $this->defaultCountryId); 
                if (!isset($this->communesMap[$communeKey])) {
                    $newCommunes[$communeKey] = [
                        'code' => $codeCm,
                        'name' => $nameCm,
                        'cercle_code_temp' => !empty($codeC) ? $codeC : null, 
                        'province_code_temp' => $codeP, 
                        'region_code_temp' => $codeR, 
                        'country_id_temp' => $this->defaultCountryId 
                    ];
                }
            } else {
                Log::warning("Skipping row due to missing Commune Code or Name: " . json_encode($row));
                $this->results['warnings'][] = "Skipping row due to missing Commune Code or Name.";
                continue;
            }

            if (!empty($codeD) && !empty($nameD)) {
                $douarKey = strtolower($codeD . '_' . $codeCm . '_' . $codeP . '_' . $codeR . '_' . $this->defaultCountryId); 
                if (!isset($this->douarsMap[$douarKey])) { 
                    $newDouars[$douarKey] = [
                        'code' => $codeD,
                        'name' => $nameD,
                        'commune_code_temp' => $codeCm, 
                        'province_code_temp' => $codeP, 
                        'region_code_temp' => $codeR, 
                        'country_id_temp' => $this->defaultCountryId 
                    ];
                }
            } else {
                Log::warning("Skipping row due to missing Douar Code or Name: " . json_encode($row));
                $this->results['warnings'][] = "Skipping row due to missing Douar Code or Name.";
                continue;
            }
        }

        if (!empty($newRegions)) {
            Log::info("Upserting " . count($newRegions) . " new regions.");
            Region::upsert(array_values($newRegions), ['code', 'country_id'], ['name']);
            $updatedRegions = Region::whereIn('code', array_column($newRegions, 'code'))
                                    ->where('country_id', $this->defaultCountryId) // Filter by country_id
                                    ->get();
            foreach ($updatedRegions as $region) {
                $this->regionsMap[strtolower($region->code . '_' . $region->country_id)] = $region->id;
            }
            $this->results['regions_created'] += count($newRegions);
            Log::info("Regions map updated. Total regions: " . count($this->regionsMap));
        }

        $provincesToUpsert = [];
        foreach ($newProvinces as $key => $provinceData) {
            $regionMapKey = strtolower($provinceData['region_code_temp'] . '_' . $provinceData['country_id_temp']);
            $regionId = $this->regionsMap[$regionMapKey] ?? null;
            Log::info("Province Upsert: Resolving region_id for province '{$provinceData['code']}'. Temp Region Code: '{$provinceData['region_code_temp']}', Temp Country ID: '{$provinceData['country_id_temp']}'. Region Map Key: '{$regionMapKey}'. Resolved Region ID: " . ($regionId ?? 'NULL'));

            if ($regionId) {
                $provincesToUpsert[] = [
                    'code' => $provinceData['code'],
                    'name' => $provinceData['name'],
                    'region_id' => $regionId
                ];
            } else {
                $this->results['warnings'][] = "Province {$provinceData['name']} (Code: {$provinceData['code']}) ignorée lors de l'upsert car la région parente n'a pas été résolue (Code Région: {$provinceData['region_code_temp']}, Country ID: {$provinceData['country_id_temp']}).";
                Log::warning("Province {$provinceData['name']} (Code: {$provinceData['code']}) skipped during upsert: Parent region not resolved (Code Région: {$provinceData['region_code_temp']}, Country ID: {$provinceData['country_id_temp']}).");
            }
        }
        if (!empty($provincesToUpsert)) {
            Log::info("Upserting " . count($provincesToUpsert) . " new provinces.");
            Province::upsert($provincesToUpsert, ['code', 'region_id'], ['name']);
            $updatedProvinces = Province::whereIn('code', array_column($provincesToUpsert, 'code'))
                                        ->whereIn('region_id', array_column($provincesToUpsert, 'region_id'))
                                        ->get();
            foreach ($updatedProvinces as $province) {
                $this->provincesMap[strtolower($province->code . '_' . $province->region_id)] = $province->id;
            }
            $this->results['provinces_created'] += count($provincesToUpsert);
            Log::info("Provinces map updated. Total provinces: " . count($this->provincesMap));
        }

        $cerclesToUpsert = [];
        foreach ($newCercles as $key => $cercleData) {
            $provinceId = null;
            $regionMapKeyForProvinceLookup = strtolower($cercleData['region_code_temp'] . '_' . $cercleData['country_id_temp']);
            $regionIdForProvinceLookup = $this->regionsMap[$regionMapKeyForProvinceLookup] ?? null;
            Log::info("Cercle Upsert: Resolving region_id for province '{$cercleData['province_code_temp']}'. Temp Region Code: '{$cercleData['region_code_temp']}', Temp Country ID: '{$cercleData['country_id_temp']}'. Region Map Key for Province Lookup: '{$regionMapKeyForProvinceLookup}'. Resolved Region ID for Province Lookup: " . ($regionIdForProvinceLookup ?? 'NULL'));

            if ($regionIdForProvinceLookup) {
                $provinceMapKey = strtolower($cercleData['province_code_temp'] . '_' . $regionIdForProvinceLookup);
                $provinceId = $this->provincesMap[$provinceMapKey] ?? null;
                Log::info("Cercle Upsert: Province Map Key: '{$provinceMapKey}'. Resolved Province ID: " . ($provinceId ?? 'NULL'));
            }

            if ($provinceId) {
                $cerclesToUpsert[] = [
                    'code' => $cercleData['code'],
                    'name' => $cercleData['name'],
                    'province_id' => $provinceId
                ];
            } else {
                $this->results['warnings'][] = "Cercle {$cercleData['name']} (Code: {$cercleData['code']}) ignoré lors de l'upsert car la province parente n'a pas été résolue (Code Province: {$cercleData['province_code_temp']}, Code Région: {$cercleData['region_code_temp']}, Country ID: {$cercleData['country_id_temp']}).";
                Log::warning("Cercle {$cercleData['name']} (Code: {$cercleData['code']}) skipped during upsert: Parent province not resolved (Code Province: {$cercleData['province_code_temp']}, Code Région: {$cercleData['region_code_temp']}, Country ID: {$cercleData['country_id_temp']}).");
            }
        }
        if (!empty($cerclesToUpsert)) {
            Log::info("Upserting " . count($cerclesToUpsert) . " new cercles.");
            Cercle::upsert($cerclesToUpsert, ['code', 'province_id'], ['name']);
            $updatedCercles = Cercle::whereIn('code', array_column($cerclesToUpsert, 'code'))
                                    ->whereIn('province_id', array_column($cerclesToUpsert, 'province_id'))
                                    ->get();
            foreach ($updatedCercles as $cercle) {
                $this->cerclesMap[strtolower($cercle->code . '_' . $cercle->province_id)] = $cercle->id;
            }
            $this->results['cercles_created'] += count($cerclesToUpsert);
            Log::info("Cercles map updated. Total cercles: " . count($this->cerclesMap));
        }

        $communesToUpsert = [];
        foreach ($newCommunes as $key => $communeData) {
            $cercleId = null;
            $provinceId = null;

            $regionMapKeyForProvinceLookup = strtolower($communeData['region_code_temp'] . '_' . $communeData['country_id_temp']);
            $regionIdForProvinceLookup = $this->regionsMap[$regionMapKeyForProvinceLookup] ?? null;
            Log::info("Commune Upsert: Resolving region_id for province '{$communeData['province_code_temp']}'. Temp Region Code: '{$communeData['region_code_temp']}', Temp Country ID: '{$communeData['country_id_temp']}'. Region Map Key for Province Lookup: '{$regionMapKeyForProvinceLookup}'. Resolved Region ID for Province Lookup: " . ($regionIdForProvinceLookup ?? 'NULL'));

            if ($regionIdForProvinceLookup) {
                $provinceMapKey = strtolower($communeData['province_code_temp'] . '_' . $regionIdForProvinceLookup);
                $provinceId = $this->provincesMap[$provinceMapKey] ?? null;
                Log::info("Commune Upsert: Province Map Key: '{$provinceMapKey}'. Resolved Province ID: " . ($provinceId ?? 'NULL'));
            }

            if (!$provinceId) { 
                $this->results['warnings'][] = "Commune {$communeData['name']} (Code: {$communeData['code']}) ignorée lors de l'upsert car la province parente n'a pas été résolue (Code Province: {$communeData['province_code_temp']}, Code Région: {$communeData['region_code_temp']}, Country ID: {$communeData['country_id_temp']}).";
                Log::warning("Commune {$communeData['name']} (Code: {$communeData['code']}) skipped during upsert: Parent province not resolved (Code Province: {$communeData['province_code_temp']}, Code Région: {$communeData['region_code_temp']}, Country ID: {$communeData['country_id_temp']}).");
                continue;
            }

            if (!empty($communeData['cercle_code_temp'])) {
                $cercleMapKey = strtolower($communeData['cercle_code_temp'] . '_' . $provinceId); // Cercle map uses code_province_id
                $cercleId = $this->cerclesMap[$cercleMapKey] ?? null;
                Log::info("Commune Upsert: Resolving cercle_id for commune '{$communeData['code']}'. Temp Cercle Code: '{$communeData['cercle_code_temp']}'. Cercle Map Key: '{$cercleMapKey}'. Resolved Cercle ID: " . ($cercleId ?? 'NULL'));

                if (!$cercleId) {
                    $this->results['warnings'][] = "Commune {$communeData['name']} (Code: {$communeData['code']}) ignorée lors de l'upsert car le cercle parent n'a pas été résolu (Code Cercle: {$communeData['cercle_code_temp']}, Province ID: {$provinceId}).";
                    Log::warning("Commune {$communeData['name']} (Code: {$communeData['code']}) skipped during upsert: Parent cercle not resolved (Code Cercle: {$communeData['cercle_code_temp']}, Province ID: {$provinceId}).");
                    $cercleId = null; 
                }
            }

            $communesToUpsert[] = [
                'code' => $communeData['code'],
                'name' => $communeData['name'],
                'cercle_id' => $cercleId, 
                'province_id' => $provinceId
            ];
        }
        if (!empty($communesToUpsert)) {
            Log::info("Upserting " . count($communesToUpsert) . " new communes.");
            Commune::upsert($communesToUpsert, ['code', 'province_id'], ['name', 'cercle_id']);
            $updatedCommunes = Commune::whereIn('code', array_column($communesToUpsert, 'code'))
                                      ->whereIn('province_id', array_column($communesToUpsert, 'province_id'))
                                      ->get();
            foreach ($updatedCommunes as $commune) {
                $this->communesMap[strtolower($commune->code . '_' . $commune->province_id)] = $commune->id;
            }
            $this->results['communes_created'] += count($communesToUpsert);
            Log::info("Communes map updated. Total communes: " . count($this->communesMap));
        }

        $douarsToUpsert = [];
        foreach ($newDouars as $key => $douarData) {
            $communeId = null;
            $provinceIdForCommuneLookup = null;
            $regionIdForCommuneLookup = null;
            $regionMapKeyForCommuneLookup = strtolower($douarData['region_code_temp'] . '_' . $douarData['country_id_temp']);
            $regionIdForCommuneLookup = $this->regionsMap[$regionMapKeyForCommuneLookup] ?? null;
            Log::info("Douar Upsert: Resolving region_id for province '{$douarData['province_code_temp']}'. Temp Region Code: '{$douarData['region_code_temp']}', Temp Country ID: '{$douarData['country_id_temp']}'. Region Map Key for Commune Lookup: '{$regionMapKeyForCommuneLookup}'. Resolved Region ID for Commune Lookup: " . ($regionIdForCommuneLookup ?? 'NULL'));

            if ($regionIdForCommuneLookup) {
                $provinceMapKeyForCommuneLookup = strtolower($douarData['province_code_temp'] . '_' . $regionIdForCommuneLookup);
                $provinceIdForCommuneLookup = $this->provincesMap[$provinceMapKeyForCommuneLookup] ?? null;
                Log::info("Douar Upsert: Province Map Key for Commune Lookup: '{$provinceMapKeyForCommuneLookup}'. Resolved Province ID for Commune Lookup: " . ($provinceIdForCommuneLookup ?? 'NULL'));
            }

            if ($provinceIdForCommuneLookup) {
                $communeMapKey = strtolower($douarData['commune_code_temp'] . '_' . $provinceIdForCommuneLookup);
                $communeId = $this->communesMap[$communeMapKey] ?? null;
                Log::info("Douar Upsert: Commune Map Key: '{$communeMapKey}'. Resolved Commune ID: " . ($communeId ?? 'NULL'));
            }

            if ($communeId) {
                $douarsToUpsert[] = [
                    'code' => $douarData['code'],
                    'name' => $douarData['name'],
                    'commune_id' => $communeId
                ];
            } else {
                $this->results['warnings'][] = "Douar {$douarData['name']} (Code: {$douarData['code']}) ignoré lors de l'upsert car la commune parente n'a pas été résolue (Code Commune: {$douarData['commune_code_temp']}, Code Province: {$douarData['province_code_temp']}, Code Région: {$douarData['region_code_temp']}, Country ID: {$douarData['country_id_temp']}).";
                Log::warning("Douar {$douarData['name']} (Code: {$douarData['code']}) skipped during upsert: Parent commune not resolved (Code Commune: {$douarData['commune_code_temp']}, Code Province: {$douarData['province_code_temp']}, Code Région: {$douarData['region_code_temp']}, Country ID: {$douarData['country_id_temp']}).");
            }
        }
        if (!empty($douarsToUpsert)) {
            Log::info("Upserting " . count($douarsToUpsert) . " new douars.");
            Douar::upsert($douarsToUpsert, ['code', 'commune_id'], ['name']);
            $updatedDouars = Douar::whereIn('code', array_column($douarsToUpsert, 'code'))
                                  ->whereIn('commune_id', array_column($douarsToUpsert, 'commune_id'))
                                  ->get();
            foreach ($updatedDouars as $douar) {
                $this->douarsMap[strtolower($douar->code . '_' . $douar->commune_id)] = $douar->id;
            }
            $this->results['douars_created'] += count($douarsToUpsert);
            Log::info("Douars map updated. Total douars: " . count($this->douarsMap));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
