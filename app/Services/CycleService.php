<?php

namespace App\Services;

use App\Models\Cycle;
use App\Repositories\CycleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * class CycleService
 */
class CycleService
{
    protected CycleRepository $cycleRepository;

    public function __construct(CycleRepository $cycleRepository)
    {
        $this->cycleRepository = $cycleRepository;
    }

    /**
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Cycle>
     */
    public function getPaginatedCycles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->cycleRepository->all($filters, $perPage);
    }

    /**
     * Get a single cycle by ID.
     *
     * @param int $id
     * @return Cycle|null
     */
    public function getCycle(int $id): ?Cycle
    {
        return $this->cycleRepository->find($id);
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return Cycle|null
     */
    public function createCycle(array $data): ?Cycle
    {
        return $this->cycleRepository->create($data);
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Cycle|null
     */
    public function updateCycle(int $id, array $data): ?Cycle
    {
        return $this->cycleRepository->update($id, $data);
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function deleteCycle(int $id): bool
    {
        return $this->cycleRepository->delete($id);
    }

    /**
     *
     * @param array<int> $ids
     * @return bool
     */
    public function bulkDeleteCycles(array $ids): bool
    {
        return $this->cycleRepository->bulkDelete($ids);
    }
}
