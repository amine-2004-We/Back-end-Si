<?php

namespace App\Repositories;

use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PurchaseRequestRepository
{
    /**
     * Get a paginated list of purchase request headers with filtering and sorting.
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        if (!empty($params['withTrashed']) && $params['withTrashed'] === 'true') {
            $query = PurchaseRequest::onlyTrashed();
        } else {
            $query = PurchaseRequest::query();
        }

        $query->with(['department', 'project', 'requester']);

        // Global search
        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%')
                    ->orWhere('observations', 'like', '%' . $searchTerm . '%');
            });
        }

        // Specific filters
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, $value);
                }
            }
        }

        // Sorting
        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * Find a PurchaseRequestHeader by ID.
     *
     * @param string $id
     * @return PurchaseRequest|Model|Collection|null
     */
    public function find(string $id): PurchaseRequest|Model|Collection|null
    {
        return PurchaseRequest::where('id', $id)->first();
    }

    /**
     * Create a new PurchaseRequestHeader.
     *
     * @param array $data
     * @return PurchaseRequest
     */
    public function create(array $data): PurchaseRequest
    {
        return PurchaseRequest::create($data);
    }

    /**
     * Update an existing PurchaseRequestHeader.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        $header = PurchaseRequest::withTrashed()->find($id);

        if (!$header) {
            throw new ModelNotFoundException("Purchase Request not found.");
        }

        if (array_key_exists('deleted_at', $data)) {
            if ($data['deleted_at'] === false && $header->trashed()) {
                $header->restore();
            } elseif ($data['deleted_at'] === true && !$header->trashed()) {
                // You could add a condition to prevent deletion if needed
                $header->delete();
            }
            unset($data['deleted_at']);
        }

        $header->update($data);

        return $header;
    }

    /**
     * Delete a PurchaseRequestHeader by ID.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $header = $this->find($id);

        if (!$header) {
            throw new ModelNotFoundException("Purchase Request not found.");
        }

        // Add checks if needed (e.g., linked lines, etc.)

        return $header->delete();
    }

    /**
     * Bulk delete headers.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return PurchaseRequest::whereIn('id', $ids)->delete();
    }

    /**
     * Get all headers with soft-deleted ones.
     *
     * @param array $filter
     * @param int $paginate
     * @return LengthAwarePaginator
     */
    public function getAllWithTrashed(array $filter = [], int $paginate = 100)
    {
        $query = PurchaseRequest::withTrashed()->with(['department', 'project', 'requester']);

        if (!empty($filter['code'])) {
            $query->where('code', 'like', '%' . $filter['code'] . '%');
        }

        return $query->paginate($paginate);
    }

    /**
     * Restore a soft-deleted PurchaseRequestHeader by ID.
     *
     * @param string $id
     * @return PurchaseRequest
     */
    public function restore(string $id): PurchaseRequest
    {
        $header = PurchaseRequest::withTrashed()->find($id);

        if (!$header) {
            throw new ModelNotFoundException("Purchase Request not found.");
        }

        $header->restore();

        return $header;
    }
}
