<?php

namespace App\Services;

use App\Models\ContractStatus;
use App\Repositories\ContractStatusRepository;

class ContractStatusService
{
    /**
     * Repository pour gérer les opérations sur les statuts de contrat
     */
    protected ContractStatusRepository $contractStatusRepository;
    public function __construct(ContractStatusRepository $contractStatusRepository)
    {
        $this->contractStatusRepository = $contractStatusRepository;
    }

    /**
     * Récupère tous les statuts de contrat non supprimés
     */

    public function all()
    {
        return $this->contractStatusRepository->all();
    }

    /**
     * Recherche un statut de contrat par son identifiant
     */

    public function findContractStatus($contractStatusId): ContractStatus
    {
        return $this->contractStatusRepository->findContractStatusById($contractStatusId);
    }

    /**
     * Crée un nouveau statut de contrat avec les données fournies
     *
     */

    public function create(array $contractStatus): ContractStatus
    {
        return $this->contractStatusRepository->create($contractStatus);
    }

    /**
     * Met à jour un statut de contrat existant par son identifiant avec les nouvelles données
     */

    public function update(string $collaborateurStatId, array $data)
    {
        return $this->contractStatusRepository->update($collaborateurStatId, $data);
    }

    /**
     * Supprime un statut de contrat par son identifiant
     */

    public function delete(string $contractStatusId): bool
    {
        return $this->contractStatusRepository->delete($contractStatusId);
    }

    /**
     * Supprime en masse plusieurs statuts de contrat par leurs identifiants
     */

    public function bulkDelete(array $ids): int
    {
        return $this->contractStatusRepository->bulkDelete($ids);
    }

    /**
     * Récupère tous les statuts de contrat, y compris ceux marqués comme supprimés (soft deleted)
     */

    public function getAllWithTrashed(){
        return $this->contractStatusRepository->getAllWithTrashed();
    }

    public function restore($contractStatId):ContractStatus
    {
        return $this->contractStatusRepository->restore($contractStatId);
    }
}
