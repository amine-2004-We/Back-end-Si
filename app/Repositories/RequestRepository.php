<?php

namespace App\Repositories;

use App\Models\RequestModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class RequestRepository
{
    /**
     * Get paginated list of Requests with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = RequestModel::query()
            ->orderBy('created_at', 'desc')
            ->orderByRaw('deleted_at IS NOT NULL');

        // Handle soft delete filters
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

        // Apply filters
        if (!empty($filters['pattern'])) {
            $query->where('pattern', 'ilike', '%' . $filters['pattern'] . '%');
        }
        if (!empty($filters['request_type_id'])) {
            $query->where('request_type_id', $filters['request_type_id']);
        }

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['request_status'])) {
            $query->where('request_status', $filters['request_status']);
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
        return RequestModel::all('id', 'pattern','amount','collaborator_id','request_type_id', 'request_status');
    }

    /**
     * Find a Request by ID, including soft deleted.
     *
     * @param int $id
     * @return RequestModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): RequestModel
    {
        return RequestModel::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new Request.
     *
     * @param array $data
     * @return RequestModel
     */
    public function create(array $data): RequestModel
    {
        return RequestModel::create($data);
    }

    /**
     * Update a Request by ID.
     *
     * @param int $id
     * @param array $data
     * @return RequestModel
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): RequestModel
    {
        $request = $this->find($id);
        $request->update($data);
        return $request;
    }

    /**
     * Delete a Request by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $request = $this->find($id);
        return $request->delete();
    }

    /**
     * Bulk delete Requests by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return RequestModel::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Request by ID.
     *
     * @param int $id
     * @return RequestModel
     */
    public function restore(int $id): RequestModel
    {
        $request = RequestModel::onlyTrashed()->findOrFail($id);
        $request->restore();
        return $request;
    }
}
