<?php

namespace App\Repositories;

use App\Models\ProvisionalAcceptance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProvisionalAcceptanceRepository
{
    protected ProvisionalAcceptance $model;

    public function __construct(ProvisionalAcceptance $model)
    {
        $this->model = $model;
    }

    /**
     * Crée le PV, ses articles, et attache le comité.
     */
    public function createWithItemsAndCommittee(array $pvData, array $itemsData, array $committeeIds, array $partialReceiptIds = []): ProvisionalAcceptance
    {
        return DB::transaction(function () use ($pvData, $itemsData, $committeeIds, $partialReceiptIds) {
            $pv = $this->model->create($pvData);
            $pv->items()->createMany($itemsData);
            $pv->committeeMembers()->sync($committeeIds);
            if (!empty($partialReceiptIds)) {
                $pv->partialReceipts()->sync($partialReceiptIds);
            }
            return $pv->load(['items.article', 'committeeMembers', 'deliveryReceipt', 'calltender', 'partialReceipts']);
        });
    }

    /**
     * Met à jour un PV, ses articles, et son comité.
     */
    public function updateWithItemsAndCommittee(int $id, array $pvData, ?array $itemsData, ?array $committeeIds, ?array $partialReceiptIds = null): ProvisionalAcceptance
    {
        $pv = $this->findById($id, false); // Ne pas inclure les relations de base

        return DB::transaction(function () use ($pv, $pvData, $itemsData, $committeeIds, $partialReceiptIds) {
            if (!empty($pvData)) {
                $pv->update($pvData);
            }

            if ($committeeIds !== null) {
                $pv->committeeMembers()->sync($committeeIds);
            }

            if ($itemsData !== null) {
                $pv->items()->delete();
                $pv->items()->createMany($itemsData);
            }

            if (is_array($partialReceiptIds)) {
                $pv->partialReceipts()->sync($partialReceiptIds);
            }

            return $pv->refresh()->load(['items.article', 'committeeMembers', 'deliveryReceipt', 'calltender', 'partialReceipts']);
        });
    }

        /**
     * Récupère tous les PV avec filtres et pagination.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with([
            'deliveryReceipt',
            'committeeMembers',
            'calltender',
            'partialReceipts'
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

        $query->orderBy('provisional_acceptance_date', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Trouve un PV par ID.
     */
    public function findById(int $id, bool $withRelations = true): ProvisionalAcceptance
    {
        $query = $this->model->query();

        if ($withRelations) {
            $query->with([
                'items.article',
                'committeeMembers',
                'deliveryReceipt',
                'calltender',
                'partialReceipts',
            ]);
        }

        return $query->findOrFail($id);
    }

    /**
     * Soft-delete un PV par son ID.
     */
    public function delete(int $id): bool
    {
        try {
            $pv = $this->findById($id, false);
            return $pv->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * Restaure un PV soft-deleted.
     */
    public function restore(int $id): ?ProvisionalAcceptance
    {
        $pv = $this->model->onlyTrashed()->find($id);
        if ($pv) {
            $pv->restore();
            return $pv->load(['items.article', 'committeeMembers', 'deliveryReceipt', 'calltender', 'partialReceipt']);
        }
        return null;
    }

    /**
     * Soft-delete plusieurs PVs par leurs IDs.
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
