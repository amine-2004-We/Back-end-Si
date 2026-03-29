<?php

namespace App\Repositories;

use App\Models\BudgetLine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * class BudgetLigneRepository
 */
class BudgetLigneRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return BudgetLine::withTrashed()
        ->with('partners', 'category')
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at')
        ->paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllWithFilters(array $filters): LengthAwarePaginator
    {
        $query = BudgetLine::query()
            ->with([
                'category',
                'projects:id'
            ])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');


        if (!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
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

        if(!empty($filters['project_name'])){
            $query->where('project.name', '=',  $filters['project_name']);
        }

        if (!empty($filters['partner_name'])) {
            $query->whereHas('partners', function ($q) use ($filters) {
                $q->where('partner_name', 'like', '%' . $filters['partner_name'] . '%');
            });
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return mixed
     */
    public function allWithoutPagination(): mixed
    {
        return BudgetLine::all('id','code');
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id): mixed
    {
        return BudgetLine::withTrashed()->find($id);
    }

    /**
     * @param array $attributes
     * @param array $partners
     * @return mixed
     */
    public function create(array $attributes): mixed
    {
        $budgetLigne = BudgetLine::create($attributes);
        return $budgetLigne;
    }


    /**
     * @param int $id
     * @param array $attributes
     * @param array $partners
     * @return mixed
     */
    public function update(int $id, array $attributes): mixed
    {
        $budgetLigne = $this->find($id);
        $budgetLigne->update($attributes);
        return $budgetLigne;
    }


    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $budgetLigne = $this->find($id);
        $budgetLigne->partners()->detach();
        $budgetLigne->delete();
        return $budgetLigne;
    }

    /**
     * @param array $ids
     * @return void
     */
    public function bulkDelete(array $ids): void
    {
        $budgetLines = BudgetLine::whereIn('id', $ids)->get();

        foreach ($budgetLines as $budgetLine) {
            $budgetLine->partners()->detach();
            $budgetLine->delete();
        }
    }

    /**
     * @param  int  $id
     * @return Response
     */
    public function restore(int $id): mixed
    {
        $budgetLigne = BudgetLine::withTrashed()->find($id);

        if (!$budgetLigne) {
            throw new ModelNotFoundException("Ligne budgétaire non trouvée.");
        }

        $exists = BudgetLine::where('code', $budgetLigne->code)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'le code est déjà utilisé par une autre ligne budgétaire.');
        }

        $budgetLigne->restore();

        return $budgetLigne;
    }

}
