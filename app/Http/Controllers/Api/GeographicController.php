<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Province;
use App\Models\Cercle;
use App\Models\Commune;
use App\Models\Douar;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeographicController extends Controller
{
    /**
     *
     * @return JsonResponse
     */
    public function getCountries(): JsonResponse
    {
        $countries = Country::orderBy('name')->get(['id', 'name']);

        return response()->json($countries);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRegions(Request $request): JsonResponse
    {
        $query = Region::query();

        $countryId = $request->query('country_id');
        if ($countryId !== null) {
            $query->where('country_id', $countryId);
        }

        $regions = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($regions);
    }

    /**
     *
     * @param int|null $regionId
     * @return JsonResponse
     */
    public function getProvinces(?int $regionId = null): JsonResponse
    {
        $query = Province::query();

        if ($regionId !== null) {
            $query->where('region_id', $regionId);
        }

        $provinces = $query->orderBy('name')->get(['id', 'name','region_id']);

        return response()->json($provinces);
    }

    /**
     *
     * @param int|null $provinceId
     * @return JsonResponse
     */
    public function getCercles(?int $provinceId = null): JsonResponse
    {
        $query = Cercle::query();

        if ($provinceId !== null) {
            $query->where('province_id', $provinceId);
        }

        $cercles = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($cercles);
    }

    /**
     *
     * @param int|null $cercleId
     * @return JsonResponse
     */
    public function getCommunes(?int $cercleId = null): JsonResponse
    {
        $query = Commune::query();

        if ($cercleId !== null) {
            $query->where('cercle_id', $cercleId);
        }

        $communes = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($communes);
    }

    /**
     * 
     * @param int|null $provinceId
     * @return JsonResponse
     */
    public function getCommunesByProvince(?int $provinceId = null): JsonResponse
    {
        $query = Commune::query();

        if ($provinceId !== null) {
            $query->where('province_id', $provinceId);
        }

        $communes = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($communes);
    }

    /**
     *
     * @param int|null $communeId
     * @return JsonResponse
     */
    public function getDouars(?int $communeId = null): JsonResponse
    {
        $query = Douar::query();

        if ($communeId !== null) {
            $query->where('commune_id', $communeId);
        }

        $douars = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($douars);
    }


    public function getUnitsByDouar(int $douarId): JsonResponse
    {
        $douar = Douar::find($douarId)->load('sites');

        if (!$douar) {
            return response()->json(['message' => 'Douar not found'], 404);
        }

        $units = $douar->sites()->with('units')->get()->flatMap(function ($site) {
            return $site->units;
        });

        return response()->json($units);
    }
}
