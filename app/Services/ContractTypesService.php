<?php

namespace App\Services;

use App\Http\Requests\StoreContractTypeRequest;
use App\Models\ContractTypes;
use App\Repositories\ContractTypesRepository;

class ContractTypesService
{
    /**
     * Repository pour gérer les opérations sur les types de contrat
     */
    protected ContractTypesRepository $contractTypesRepository;
    public function __construct(ContractTypesRepository $contractTypesRepository){
        $this->contractTypesRepository = $contractTypesRepository;
    }

    /**
     * Récupère tous les types de contrat non supprimés
     */

    public function all()
    {
        return $this->contractTypesRepository->all();
    }

    /**
     * Crée un nouveau type de contrat avec les données fournies
     */

    public function create(array $data){
        return $this->contractTypesRepository->create($data);
    }

    /**
     * Met à jour un type de contrat existant avec les nouvelles données
     */

    public function update(array $data, $id){
        return $this->contractTypesRepository->update($id,$data);
    }

    /**
     * Supprime un type de contrat par son identifiant
     */

    public function delete($id)
    {
        return $this->contractTypesRepository->delete($id);
    }

    /**
     * Récupère un type de contrat par son identifiant
     */

    public function getContractTypebyId($id){
        return $this->contractTypesRepository->find($id);
    }

    /**
     * Supprime en masse plusieurs types de contrat par leurs identifiants
     */

    public function bulkDelete(array $ids): int
    {
        return $this->contractTypesRepository->bulkDelete($ids);
    }

    /**
     * Récupère tous les types de contrat, y compris ceux supprimés (soft deleted)
     */

    public function getAllWithTrashed(){
        return $this->contractTypesRepository->getAllWithTrashed();
    }

    public function restore(int $contractTypeId): ContractTypes
    {
        return $this->contractTypesRepository->restore($contractTypeId);
    }
}
