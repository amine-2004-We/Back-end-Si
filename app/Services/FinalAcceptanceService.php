<?php

namespace App\Services;

use App\Models\FinalAcceptance;
use App\Repositories\FinalAcceptanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class FinalAcceptanceService
{
    protected FinalAcceptanceRepository $repository;

    public function __construct(FinalAcceptanceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($filters, $perPage);
    }

    public function findById(int $id): FinalAcceptance
    {
        return $this->repository->findById($id);
    }

    /**
     * Crée un nouveau PV Définitif.
     */
    public function create(array $data): FinalAcceptance
    {
        try {
            $articleIds = $data['article_ids'];
            $committeeIds = $data['committee_ids'];
            $provisionalAcceptanceIds = $data['provisional_acceptance_ids'];
            unset($data['article_ids'], $data['committee_ids'], $data['provisional_acceptance_ids']);

            $data['identifier'] = $this->generateIdentifier($data['calltender_id']);

            return $this->repository->create($data, $articleIds, $committeeIds, $provisionalAcceptanceIds);

        } catch (Exception $e) {
            Log::error("Erreur création PV Définitif: " . $e->getMessage());
            throw new Exception("La création du PV Définitif a échoué.");
        }
    }

    /**
     * Met à jour un PV Définitif.
     */
    public function update(int $id, array $data): FinalAcceptance
    {
        try {
            $articleIds = $data['article_ids'] ?? null;
            $committeeIds = $data['committee_ids'] ?? null;
            $provisionalAcceptanceIds = $data['provisional_acceptance_ids'] ?? null;
            unset($data['article_ids'], $data['committee_ids'], $data['provisional_acceptance_ids']);

            return $this->repository->update($id, $data, $articleIds, $committeeIds, $provisionalAcceptanceIds);

        } catch (Exception $e) {
            Log::error("Erreur mise à jour PV Définitif: " . $e->getMessage());
            throw new Exception("La mise à jour du PV Définitif a échoué.");
        }
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function restore(int $id): ?FinalAcceptance
    {
        return $this->repository->restore($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    /**
     * Génère un identifiant unique: PVRDEF-[ID Marché]-[NuméroSéquentiel]
     */
    protected function generateIdentifier(int $calltenderId): string // <-- CORRIGÉ
    {
        $prefix = "PVRDEF-{$calltenderId}-";

        $latest = FinalAcceptance::where('identifier', 'like', $prefix . '%')
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
