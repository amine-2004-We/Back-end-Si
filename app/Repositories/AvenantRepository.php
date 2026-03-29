<?php

namespace App\Repositories;

use App\Models\Avenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AvenantRepository
{
    /**
     * Get filtered avenants based on parameters.
     *
     * @param array $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredAvenants(array $params): LengthAwarePaginator|Collection
    {
        // 1. Get the activation_status filter, default to 'active'
        $activationStatus = $params['filter']['activation_status'] ?? 'active';

        // 2. Initialize the query based on activation_status
        $query = Avenant::query(); // Base query

        if ($activationStatus === 'deactivated') {
            $query->onlyTrashed(); // Only show soft-deleted
        } elseif ($activationStatus === 'all') {
            $query->withTrashed(); // Show all (active and soft-deleted)
        }
        // If 'active', the default Avenant::query() is correct (it implies whereNull('deleted_at'))

        // Eager-load relationships
        $query->with(['marche', 'responsible']);

        // Handle Global Search
        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('avenant_id', 'ilike', '%' . $searchTerm . '%');
            });
        }

        // Handle Specific Column Filters
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                
                // We already handled activation_status, so skip it in this loop
                if ($key === 'activation_status' || !$value) {
                    continue;
                }

                // Handle other filters
                $query->where($key, $value);
            }
        }

        // Handle Sorting
        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle Pagination
        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new avenant.
     *
     * @param array $data
     * @return Avenant
     */
    public function create(array $data): Avenant
    {
        return Avenant::create($data);
    }

    /**
     * Find an avenant by its ID.
     *
     * @param int $id
     * @return Avenant|null
     */
    public function find(int $id): ?Avenant
    {
        return Avenant::find($id);
    }

    /**
     * Update an avenant by its ID.
     *
     * @param int $id
     * @param array $data
     * @return Avenant
     */
    public function update(int $id, array $data): Avenant
    {
        $avenant = $this->find($id);
        if (!$avenant) {
            throw new ConflictHttpException('Avenant not found');
        }
        $avenant->update($data);
        return $avenant;
    }

    /**
     * Delete an avenant by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $avenant = $this->find($id);
        if (!$avenant) {
            throw new ConflictHttpException('Avenant non trouvé');
        }

        return $avenant->delete();
    }

    /**
     * Restore a soft-deleted avenant by its ID.
     *
     * @param int $id
     * @return Avenant
     */
    public function restore(int $id): Avenant
    {
        $avenant = Avenant::withTrashed()->find($id);
        if (!$avenant) {
            throw new ConflictHttpException('Avenant not found');
        }
        $avenant->restore();
        return $avenant;
    }

    /**
     * Bulk delete avenants by their IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        $avenants = Avenant::whereIn('id', $ids)->get();
        if ($avenants->isEmpty()) {
            throw new ConflictHttpException('No avenants found for the provided IDs');
        }

        foreach ($avenants as $avenant) {
            $avenant->delete();
        }

        return $avenants->count();
    }
}
