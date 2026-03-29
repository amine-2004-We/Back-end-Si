<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SupplierRepository
{
    /**
     * Get filtered suppliers based on parameters.
     *
     * @param array $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredSuppliers(array $params): LengthAwarePaginator|Collection
    {

        $query = Supplier::query()->with([
            'contactPeople',
        ]);

        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query->onlyTrashed();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                
                $q->where('company_name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('email', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }
    /**
     * Create a new supplier.
     *
     * @param array $data
     * @return Supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Find a supplier by its ID.
     *
     * @param int $id
     * @return Supplier|null
     */
    public function find(int $id): ?Supplier
    {

        //load the supplier with its contact people
        return Supplier::with('contactPeople')->find($id);
    }
    
    /**
     * Find a supplier by its ID with all statuses.
     *
     * @param int $id
     * @return Supplier|null
     */
    public function findWithTrashed(int $id): ?Supplier
    {
        return Supplier::withTrashed()->find($id);
    }


    /**
     * Update a supplier by its ID.
     *
     * @param int $id
     * @param array $data
     * @return Supplier
     * @throws ConflictHttpException
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = $this->find($id);
        if (!$supplier) {
            throw new ConflictHttpException('Supplier not found');
        }
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete a supplier by its ID (soft delete).
     *
     * @param int $id
     * @return bool|null
     * @throws ConflictHttpException
     */
    public function delete(int $id): ?bool
    {
        $supplier = $this->find($id);
        if (!$supplier) {
            throw new ConflictHttpException('Supplier not found');
        }
        return $supplier->delete();
    }

    /**
     * Restore a soft-deleted supplier by its ID.
     *
     * @param int $id
     * @return Supplier
     * @throws ConflictHttpException
     */
    public function restore(int $id): Supplier
    {
        $supplier = $this->findWithTrashed($id);
        if (!$supplier) {
            throw new ConflictHttpException('Supplier not found');
        }
        $supplier->restore();
        return $supplier;
    }

    /**
     * Bulk soft delete suppliers by their IDs.
     *
     * @param array $ids
     * @return int
     * @throws ConflictHttpException
     */
    public function bulkDelete(array $ids): int
    {
        $suppliers = Supplier::whereIn('id', $ids)->get();
        if ($suppliers->isEmpty()) {
            throw new ConflictHttpException('No suppliers found for the provided IDs');
        }

        foreach ($suppliers as $supplier) {
            $supplier->delete();
        }

        return $suppliers->count();
    }
}
