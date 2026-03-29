<?php

namespace App\Repositories;

use App\Models\BudgetCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * class BudgetCategoryRepository
 */
class BudgetCategoryRepository
{
    /**
     * @return mixed
     */
    public function all()
    {
        return BudgetCategory::paginate(10);
    }

    public function withFilters(array $filters) {
        $query = BudgetCategory::query()
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['type'])) {
            $query->where('type', '=', $filters['type'] );
        }

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

        if(!empty($filters['label'])) {
            $query->where('label', 'like', '%' . $filters['label'] . '%');
        }

        if(!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
        }

        if(!empty($filters['budgetary_area'])) {
            $query->where('budgetary_area', 'like', '%' . $filters['budgetary_area'] . '%');
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;


        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return BudgetCategory::all('id','label','code');
    }

    /**
     * @param int $id
     * @return BudgetCategory
     */
    public function find(int $id): BudgetCategory
    {
        return BudgetCategory::find($id);
    }

    public function findWithTrashed(int $id): BudgetCategory
    {
        return BudgetCategory::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return BudgetCategory
     */
    public function create(array $data): BudgetCategory
    {
        $budgetCategory = new BudgetCategory();
        $budgetCategory->fill($data);
        $budgetCategory->save();
        return $budgetCategory;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $budgetCategory = $this->find($id);
        $budgetCategory->update($data);
        return $budgetCategory;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $budgetCategory = $this->find($id);
        $budgetCategory->delete();
        return $budgetCategory;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public  function bulkDelete(array $ids): mixed
    {
        return BudgetCategory::destroy($ids['budget_category_ids']);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */


    public function restore(int $id): mixed
    {
        $budgetCategory = BudgetCategory::withTrashed()->find($id);

        if (!$budgetCategory) {
            throw new ModelNotFoundException("Catégorie budgétaire non trouvée.");
        }

        $codeExists = BudgetCategory::where('code', $budgetCategory->code)
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->exists();

        $labelExists = BudgetCategory::where('label', $budgetCategory->label)
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->exists();

        $conflicts = [];

        if ($codeExists) {
            $conflicts[] = 'code';
        }

        if ($labelExists) {
            $conflicts[] = 'label';
        }

        if (!empty($conflicts)) {
            $message = 'Impossible de restaurer : les champs suivants sont déjà utilisés : ' . implode(', ', $conflicts) . '.';
            abort(Response::HTTP_CONFLICT, $message);
        }

        $budgetCategory->restore();

        return $budgetCategory;
    }

}
