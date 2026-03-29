<?php

namespace App\Repositories;
use App\Models\Departement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * class DepartmentRepository
 */
class DepartmentRepository
{
    /**
     * @return mixed
     */
    public function create(array $data): Departement
    {
        return Departement::create($data);
    }
    /**
     * Get all departments with pagination.
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $params): LengthAwarePaginator
{
    // Eager load the relationship for efficiency
    $query = Departement::with('typeDepartement');

     // 1. Initialize the query based on the status FIRST.
    if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
        $query = Departement::onlyTrashed();
    }
    else {
        $query = Departement::query();
    }

    // Handle searching by name (case-insensitive)
    if (!empty($params['search'])) {
        $query->where('name', 'ilike', '%' . $params['search'] . '%');
    }

    // A default sort order is good practice
    $query->orderBy('name', 'asc');

    // Handle Pagination
    $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;

    return $query->paginate($perPage);
}
    /**
     * Find a department by its ID.
     * @param int $id
     * @return Departement|null
     */
    public function find(int $id): ?Departement
    {
        return Departement::find($id);
    }
    /**
     * Update a department by its ID.
     * @param int $id
     * @param array $data
     * @return Departement
     */
    public function update(int $id, array $data): Departement
    {
        $department = $this->find($id);
        if (!$department) {
            throw new ModelNotFoundException("Department not found.");
        }
        $department->update($data);
        return $department;
    }
    /**
     * Delete a department by its ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $department = $this->find($id);
        if (!$department) {
            throw new ModelNotFoundException("Department not found.");
        }
        return $department->delete();
    }
    /**
     * Restore a deleted department by its ID.
     * @param int $id
     * @return Departement
     */
    public function restore(int $id): Departement
    {   
        $department = Departement::withTrashed()->find($id);
        if (!$department) {
            throw new ModelNotFoundException("Department not found.");
        }
        $department->restore();
        return $department;
    }
    /**
     * Bulk delete departments by their IDs.
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Departement::destroy($ids);
    }
    

}