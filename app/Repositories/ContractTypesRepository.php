<?php

namespace App\Repositories;

use App\Http\Requests\StoreContractTypeRequest;
use App\Models\ContractTypes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractTypesRepository
{
    /**
     * Récupérer tous les types d'un contrat avec pagination.
     */

    public function all(int $paginate = 10): LengthAwarePaginator
    {
        return ContractTypes::paginate($paginate);
    }

    /**
     * Créer un nouveau type d'un contrat.
     */

    public function create(array $data): ContractTypes
    {
        return ContractTypes::create($data);
    }

    /**
     * Mettre à jour les données d'un type d'un contrat.
     */

    public function update(string $contractTypeId, array $data): ContractTypes
    {
        $contractType = ContractTypes::findOrFail($contractTypeId);
        $contractType->update($data);
        return $contractType;
    }

    /**
     * Supprimer plusieurs types d'un contrat par leurs IDs.
     */

    public function bulkDelete(array $ids): int
    {
        return ContractTypes::whereIn('id', $ids)->delete();
    }

    /**
     * Trouver un type d'un contrat par ID (ou retourner null si non trouvé).
     */

    public function find(string $contractTypeId): ?ContractTypes
    {
        return ContractTypes::find($contractTypeId);
    }

    /**
     * Supprimer un type par son ID.
     */

    public function delete(string $contractTypeId): bool
    {
        $contractType = $this->find($contractTypeId);
        if (!$contractType) {
            throw new ModelNotFoundException("Contract type not found.");
        }

        return $contractType->delete();
    }

    /**
     * Récupérer tous les types d'un contrat, y compris les supprimés (soft deleted).
     */

    public function getAllWithTrashed($paginate = 10): LengthAwarePaginator
    {
        return ContractTypes::onlyTrashed()->paginate($paginate);
    }

    public function restore(int $contractTypeId): ContractTypes
    {
        $contractType = ContractTypes::withTrashed()->findOrFail($contractTypeId);
        $contractType->restore();
        return $contractType;
    }


}
