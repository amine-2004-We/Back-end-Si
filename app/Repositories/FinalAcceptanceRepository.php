<?php

namespace App\Repositories;

use App\Models\FinalAcceptance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class FinalAcceptanceRepository
{
    protected FinalAcceptance $model;

    public function __construct(FinalAcceptance $model)
    {
        $this->model = $model;
    }

    /**
     * Crée le PV Définitif et synchronise les listes.
     */
    public function create(array $pvData, array $articleIds, array $committeeIds, array $provisionalAcceptanceIds): FinalAcceptance
    {
        return DB::transaction(function () use ($pvData, $articleIds, $committeeIds, $provisionalAcceptanceIds) {
            $pv = $this->model->create($pvData);
            $pv->articles()->sync($articleIds);
            $pv->committeeMembers()->sync($committeeIds);
            $pv->provisionalAcceptances()->sync($provisionalAcceptanceIds);

            return $pv->load(['articles', 'committeeMembers', 'provisionalAcceptances', 'calltender']);
        });
    }

    /**
     * Met à jour le PV Définitif et ses listes.
     */
    public function update(int $id, array $pvData, ?array $articleIds, ?array $committeeIds, ?array $provisionalAcceptanceIds): FinalAcceptance
    {
        $pv = $this->findById($id, false);

        return DB::transaction(function () use ($pv, $pvData, $articleIds, $committeeIds, $provisionalAcceptanceIds) {
            if (!empty($pvData)) {
                $pv->update($pvData);
            }
            if ($articleIds !== null) {
                $pv->articles()->sync($articleIds);
            }
            if ($committeeIds !== null) {
                $pv->committeeMembers()->sync($committeeIds);
            }
            if ($provisionalAcceptanceIds !== null) {
                $pv->provisionalAcceptances()->sync($provisionalAcceptanceIds);
            }

            return $pv->refresh()->load(['articles', 'committeeMembers', 'provisionalAcceptances', 'calltender']);
        });
    }

    /**
     * Récupère tous les PV avec filtres et pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with([
            'provisionalAcceptances',
            'calltender',
            'committeeMembers'
        ]);

        if (($filters['status_filter'] ?? 'active') === 'all') {
            $query->withTrashed();
        } elseif (($filters['status_filter'] ?? 'active') === 'trashed') {
            $query->onlyTrashed();
        }

        $query->when($filters['identifier'] ?? null, function ($q, $identifier) {
            return $q->where('identifier', 'like', '%' . $identifier . '%');
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            return $q->where('status', $status);
        });

        $query->orderBy('final_acceptance_date', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Trouve un PV par ID.
     */
    public function findById(int $id, bool $withRelations = true): FinalAcceptance
    {
        $query = $this->model->query();
        if ($withRelations) {
            $query->with(['articles', 'committeeMembers', 'provisionalAcceptances', 'calltender']);
        }
        return $query->findOrFail($id);
    }

    public function delete(int $id): bool
    {
        try {
            $pv = $this->findById($id, false);
            return $pv->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    public function restore(int $id): ?FinalAcceptance
    {
        $pv = $this->model->onlyTrashed()->find($id);
        if ($pv) {
            $pv->restore();
            return $pv->load(['articles', 'committeeMembers', 'provisionalAcceptance', 'calltender']);
        }
        return null;
    }

    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
