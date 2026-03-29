<?php

namespace App\Services;

use App\Models\Cheque;
use App\Repositories\ChequeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 *class ChequeService
 */
class ChequeService
{
    /**
     * @var ChequeRepository
     */
    protected ChequeRepository $chequeRepository;

    /**
     * @param ChequeRepository $chequeRepository
     */
    public function __construct(ChequeRepository $chequeRepository)
    {
        $this->chequeRepository = $chequeRepository;
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedCheques(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->chequeRepository->getPaginated($filters, $perPage);
    }

    /**
     * @param int $id
     * @return Cheque|null
     */
    public function getCheque(int $id): ?Cheque
    {
        return $this->chequeRepository->findById($id);
    }

    /**
     * @param array $data
     * @return Cheque|null
     */
    public function createCheque(array $data): ?Cheque
    {
        return DB::transaction(function () use ($data) {
            return $this->chequeRepository->create($data);
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return Cheque|null
     */
    public function updateCheque(int $id, array $data): ?Cheque
    {
        $cheque = $this->chequeRepository->findById($id);
        if (!$cheque) {
            return null;
        }

        $this->chequeRepository->update($cheque, $data);
        return $cheque->fresh();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleChequeActivation(array $ids): array
    {
        return $this->chequeRepository->toggleActivation($ids);
    }
}
