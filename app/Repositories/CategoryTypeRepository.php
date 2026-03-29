<?php

namespace App\Repositories;

use App\Models\CategoryType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * class CategoryTypeRepository
 */
class CategoryTypeRepository
{
    /**
     * @return mixed
     */
    public function all()
    {
        return CategoryType::paginate(10);
    }

    /**
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function withFilters(array $filters) {
        $query = CategoryType::query()
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

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

        if(!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }


        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return CategoryType::all('id','name');
    }

    /**
     * @param int $id
     * @return CategoryType
     */
    public function find(int $id): CategoryType
    {
        return CategoryType::find($id);
    }

    public function findWithTrashed(int $id): CategoryType
    {
        return CategoryType::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return CategoryType
     */
    public function create(array $data): CategoryType
    {
        $categoryType = new CategoryType();
        $categoryType->fill($data);
        $categoryType->save();
        return $categoryType;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $categoryType = $this->find($id);
        $categoryType->update($data);
        return $categoryType;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $categoryType = $this->find($id);
        $categoryType->delete();
        return $categoryType;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public  function bulkDelete(array $ids): mixed
    {
        return CategoryType::destroy($ids['category_type_ids']);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */


    public function restore(int $id): mixed
    {
        $categoryType = CategoryType::withTrashed()->find($id);

        if (!$categoryType) {
            throw new ModelNotFoundException("Type de rubrique non trouvé.");
        }

                $nameExists = CategoryType::where('name', $categoryType->name)
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->exists();



        if ($nameExists) {
            $message = 'Impossible de restaurer : le nom est déjà utilisé.';
            abort(Response::HTTP_CONFLICT, $message);
        }

        $categoryType->restore();

        return $categoryType;
    }
}
