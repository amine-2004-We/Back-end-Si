<?php 

namespace App\Services;

use App\Repositories\DepartmentRepository;
use App\Models\Departement;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @var DepartmentRepository $departmentRepository
 */
class DepartmentService
{
    protected DepartmentRepository $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }
    /**
     * get filtered departments based on parameters.
     * @param array $params
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFilteredDepartments(array $params): LengthAwarePaginator
    {
        return $this->departmentRepository->getFiltered($params);
    }
    /**
     * Create a new department.
     * @param array $data
     * @return Departement
     */
    public function create(array $data): Departement
    {
        return $this->departmentRepository->create($data);
    }
    /**
     * Get a single department by its ID.
     * @param int $id
     * @return Departement|null
     */
    public function getById(int $id): ?Departement
    {
        return $this->departmentRepository->find($id);
    }
    /**
     * Update a department by its ID.
     * @param int $id
     * @param array $data
     * @return Departement
     */
    public function update(int $id, array $data): Departement
    {
        return $this->departmentRepository->update($id, $data);
    }
    /**
     * Delete a department by its ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->departmentRepository->delete($id);
    }
    /**
     * Restore a deleted department by its ID.
     * @param int $id
     * @return Departement
     */
    public function restore(int $id): Departement
    {
        return $this->departmentRepository->restore($id);
    }
    /**
     * Bulk delete departments by their IDs.
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->departmentRepository->bulkDelete($ids);
    }
}