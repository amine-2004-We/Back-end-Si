<?php

namespace App\Repositories;
use App\Models\TypeDepartement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery\Matcher\Type;

class TypeDepartmentRepository
{
    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredDepartments(array $params): LengthAwarePaginator
    {
        // Implement filtering logic here
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = TypeDepartement::onlyTrashed();
        } else {
            $query = TypeDepartement::query();
        }
        // Handle searching by name (case-insensitive)
        if (!empty($params['search'])) {
            $query->where('name', 'ilike', '%' . $params['search'] . '%');
        }

        // A default sort order is good practice
        $query->orderBy('name');

        // Handle Pagination
        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;
        return $query->paginate($perPage);

    }
    /**
     * @param array $data
     * @return TypeDepartement
     */
    public function create(array $data): TypeDepartement
    {
        return TypeDepartement::create($data);
    }
    /**
     * @param int $id
     * @return TypeDepartement|null
     */
    public function find(int $id): ?TypeDepartement
    {
        return TypeDepartement::find($id);
    }

    /**
     * @param array $data
     * @return TypeDepartement
     */
    public function update(int $id, array $data): TypeDepartement
    {
        $typeDepartement = TypeDepartement::find($id);
        $typeDepartement->update($data);
        return $typeDepartement;
    }
    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $typeDepartement = TypeDepartement::find($id);
        if (!$typeDepartement) {
            throw new ModelNotFoundException("Type Departement not found.");
        }
        return $typeDepartement->delete();
    }
    /**
     * Restore a deleted type department by its ID.
     * @param int $id
     * @return TypeDepartement
     */
    public function restore(int $id): TypeDepartement
    {
        $typeDepartement = TypeDepartement::withTrashed()->find($id);
        if (!$typeDepartement) {
            throw new ModelNotFoundException("Type Departement not found.");
        }
      $typeDepartement->restore();
        return $typeDepartement;
    }
    /**
     * Bulk delete type departments by their IDs.
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return TypeDepartement::destroy($ids);
    }



}
