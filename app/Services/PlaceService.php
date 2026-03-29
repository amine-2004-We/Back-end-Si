<?php

namespace App\Services;

use App\Models\Place;
use App\Repositories\PlaceRepository;
use App\Traits\UploadFileTrait;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Models\Region;

/**
 * class PlaceService
 */
class PlaceService
{
    /**
     * @param PlaceRepository $placeRepository
     */
    public function __construct(protected PlaceRepository $placeRepository)
    {
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'province_id',
            'is_active',
            'name',
            'type',
            'status',
            'latitude',
            'latitude_from',
            'latitude_to',
            'longitude',
            'longitude_from',
            'longitude_to',
            'address',
            'per_page',
        ]);
        return $this->placeRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->placeRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id): mixed
    {
        return $this->placeRepository->find($id);
    }

   /**
     * @param array $data
     * @param StorePlaceRequest $request
     * @return mixed
     */
    public function create(array $data): mixed
    {
        return $this->placeRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @param UpdatePlaceRequest $request
     * @return Place
     */
    public function update(int $id, array $data): Place
    {
        return $this->placeRepository->update($data, $id);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        return $this->placeRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->placeRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->placeRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

     /**
     * @return array{
     */
    public function options(): array
    {
        $regions = Region::with('provinces')->get();
        $types = ['Centre de formation externe', 'Gratuit', 'Payant', 'Salle de formation ZA'];
        $statues = ['Actif', 'Annulé', 'En pause', 'Clôturé'];

        return [
            'regions' => $regions,
            'types' => $types,
            'statues' => $statues,
        ];
    }
}
