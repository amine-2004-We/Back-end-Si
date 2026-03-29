<?php

namespace App\Repositories;

use App\Models\RequestType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class RequestTypeRepository
{
    /**
     * Get paginated list of RequestTypes with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = RequestType::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        if (!empty($filters['type_name'])) {
            $query->where('type_name', 'like', '%' . $filters['type_name'] . '%');
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all request types with only id and type_name.
     *
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return RequestType::all('id', 'type_name');
    }

    /**
     * Find a RequestType by ID, including soft deleted.
     *
     * @param int $id
     * @return RequestType
     * @throws ModelNotFoundException
     */
    public function find(int $id): RequestType
    {
        return RequestType::withTrashed()->findOrFail($id);
    }

    /**
     * Get all request types without pagination (id and type_name).
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return RequestType::all('id', 'type_name');
    }

    /**
     * Create a new RequestType.
     *
     * @param array $data
     * @return RequestType
     */
    public function create(array $data): RequestType
    {
        return RequestType::create($data);
    }

    /**
     * Update a RequestType by ID.
     *
     * @param int $id
     * @param array $data
     * @return RequestType
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): RequestType
    {
        $type = $this->find($id);
        $type->update($data);
        return $type;
    }

    /**
     * Delete a RequestType by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $type = $this->find($id);
        return $type->delete();
    }

    /**
     * Bulk delete RequestTypes by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return RequestType::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted RequestType by ID.
     *
     * @param int $id
     * @return RequestType
     */
    public function restore(int $id): RequestType
    {
        $type = RequestType::onlyTrashed()->findOrFail($id);

        $exists = RequestType::where('type_name', $type->type_name)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'Ce type de requête existe déjà');
        }

        $type->restore();

        return $type;
    }
}
