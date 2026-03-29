<?php

namespace App\Repositories;

use App\Models\ContractStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractStatusRepository
{
    /**
     * Récupérer tous les statuts d'un contrat avec pagination.
     */

    public function all(int $paginate = 10): LengthAwarePaginator
    {
        return ContractStatus::paginate($paginate);
    }

    /**
     * Trouver les statuts d'un contrat par son ID.
     */

    public function findContractStatusById(string $contractStatId): ContractStatus
    {
        return ContractStatus::findOrFail($contractStatId);
    }

    /**
     * Créer un nouveau statut d'un contrat.
     */

    public function create(array $data): ContractStatus
    {
        return ContractStatus::create($data);
    }

    /**
     * Mettre à jour les données d'un statut d'un contrat.
     */

    public function update(string $contractStatId, array $data): ContractStatus
    {
        $contractStatus = ContractStatus::findOrFail($contractStatId);
        $contractStatus->update($data);
        return $contractStatus;
    }

    /**
     * Supprimer plusieurs statuts d'un contrat par leurs IDs.
     */

    public function bulkDelete(array $ids): int
    {
        return ContractStatus::whereIn('id', $ids)->delete();
    }

    /**
     * Trouver un statut d'un contrat par ID (ou retourner null si non trouvé).
     */

    public function find(string $contractStatId): ?ContractStatus
    {
        return ContractStatus::find($contractStatId);
    }

    /**
     * Supprimer un statut par son ID.
     */

    public function delete(string $contractStatId): bool
    {
        $contractStat = $this->find($contractStatId);
        if (!$contractStat) {
            throw new ModelNotFoundException("Contract stat not found.");
        }

        return $contractStat->delete();
    }

    /**
     * Récupérer tous les statuts d'un contrat, y compris les supprimés (soft deleted).
     */

    public function getAllWithTrashed($paginate = 10): LengthAwarePaginator
    {
        return ContractStatus::onlyTrashed()->paginate($paginate);
    }

    public function restore(int $contractStatId):ContractStatus
    {
        $contractStat=ContractStatus::onlyTrashed()->findOrFail($contractStatId);
        $contractStat->restore();
        return $contractStat;
    }

}
