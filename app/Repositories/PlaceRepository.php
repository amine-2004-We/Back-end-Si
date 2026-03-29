<?php

namespace App\Repositories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
    * class PlaceRepository
 */
class PlaceRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return Place::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
    public function withFilters(array $filters): mixed
    {
        $query = Place::query()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at')
            ->with('province', 'createdBy');

        if (!empty($filters['province_id'])) {
            $query->where('province_id', '=', $filters['province_id']);
        }

        $filters['is_active'] = $filters['is_active'] ?? 'true';

        $query = match ($filters['is_active']) {
            'true' => $query->withoutTrashed(),
            'false' => $query->onlyTrashed(),
            default => $query->withTrashed(),
        };

        if(!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if(!empty($filters['type'])) {
            $query->where('type', '=', $filters['type']);
        }

        if(!empty($filters['status'])){
            $query->where('status','=', $filters['status']);
        }

        if (!empty($filters['latitude'])) {
            $query->where('latitude', $filters['latitude']);
        }

        if (!empty($filters['latitude_from'])) {
            $query->where('latitude', '>=', $filters['latitude_from']);
        }

        if (!empty($filters['latitude_to'])) {
            $query->where('latitude', '<=', $filters['latitude_to']);
        }

        if (!empty($filters['longitude'])) {
            $query->where('longitude', $filters['longitude']);
        }
        if (!empty($filters['longitude_from'])) {
            $query->where('longitude', '>=', $filters['longitude_from']);
        }
        if (!empty($filters['longitude_to'])) {
            $query->where('longitude', '<=', $filters['longitude_to']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Place::all('id','province_id','name','type', 'status', 'capacity', 'latitude', 'longitude', 'address');
    }

    /**
     * @param int $id
     * @return Place
     */
    public function find(int $id): Place
    {
        return Place::with('province', 'createdBy')->findOrFail($id);
    }

    /**
     * @param int $id
     * @return Place
     */
    public function findWithTrashed(int $id): Place
    {
        return Place::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return Place
     */
    public function create(array $data): Place
    {
        $place = Place::create($data);
        return $place;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $place = $this->find($id);
        $place->update($data);
        return $place;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $place = $this->find($id);
        $place->delete();
        return $place;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return Place::destroy($ids);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(int $id): mixed
    {
        $place = $this->findWithTrashed($id);

        if (!$place) {
            abort(404, 'Place not found.');
        }

        $place->restore();
        return $place;
    }
}
