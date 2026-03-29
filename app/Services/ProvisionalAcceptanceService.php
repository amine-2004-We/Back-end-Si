<?php

namespace App\Services;

use App\Models\ProvisionalAcceptance;
use App\Repositories\ProvisionalAcceptanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class ProvisionalAcceptanceService
{
    protected ProvisionalAcceptanceRepository $repository;

    public function __construct(ProvisionalAcceptanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Récupère les PV paginés.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($filters, $perPage);
    }

    /**
     * Trouve un PV par son ID.
     */
    public function findById(int $id): ProvisionalAcceptance
    {
        return $this->repository->findById($id);
    }

    /**
     * Crée un nouveau PV Provisoire.
     */
    public function create(array $data): ProvisionalAcceptance
    {
        try {
            $itemsData = $data['items'];
            $committeeIds = $data['committee_ids'];
            $partialReceiptIds = $data['partial_receipt_ids'] ?? [];
            unset($data['items'], $data['committee_ids'], $data['partial_receipt_ids']);

            $data['identifier'] = $this->generateIdentifier($data['calltender_id']);

            return $this->repository->createWithItemsAndCommittee($data, $itemsData, $committeeIds, $partialReceiptIds);

        } catch (Exception $e) {
            Log::error("Erreur création PV Provisoire: " . $e->getMessage());
            throw new Exception("La création du PV Provisoire a échoué.");
        }
    }

    /**
     * Met à jour un PV Provisoire.
    */
    
    public function update(int $id, array $data): ProvisionalAcceptance
    {
        try {
            $itemsData = $data['items'] ?? null;
            $committeeIds = $data['committee_ids'] ?? null;
            $partialReceiptIds = $data['partial_receipt_ids'] ?? null;
            unset($data['items'], $data['committee_ids'], $data['partial_receipt_ids']);

            return $this->repository->updateWithItemsAndCommittee($id, $data, $itemsData, $committeeIds, $partialReceiptIds);

        } catch (Exception $e) {
            Log::error("Erreur mise à jour PV Provisoire: " . $e->getMessage());
            throw new Exception("La mise à jour du PV Provisoire a échoué.");
        }
    }

    /**
     * Soft-delete un PV.
     */
    
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Restaure un PV soft-deleted.
     */
    public function restore(int $id): ?ProvisionalAcceptance
    {
        return $this->repository->restore($id);
    }

    /**
     * Soft-delete plusieurs PVs.
     */
    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    /**
     * Génère un identifiant unique PVRVS-[ID BR]-[Numéro].
     */
    protected function generateIdentifier(int $calltenderId): string
    {
        $prefix = "PVRPVS-{$calltenderId}-";

        $latest = ProvisionalAcceptance::where('identifier', 'like', $prefix . '%')
                                       ->orderBy('identifier', 'desc')
                                       ->withTrashed()
                                       ->first();

        $nextNumber = 1;
        if ($latest) {
            $lastNumberPart = str_replace($prefix, '', $latest->identifier);
            if (is_numeric($lastNumberPart)) {
                $nextNumber = intval($lastNumberPart) + 1;
            }
        }

        $sequenceNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $sequenceNumber;
    }
}
