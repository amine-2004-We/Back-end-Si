<?php

namespace App\Services;

use App\Models\Phase;
use App\Repositories\PhaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PhaseService
{
    public function __construct(protected PhaseRepository $phaseRepository)
    {
    }

    /**
     * Summary of getPaginatedPhases
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPhases(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->phaseRepository->getPaginated($filters, $perPage);
    }

    /**
     * Summary of findPhaseById
     * @param int $id
     * @return Phase|\Illuminate\Database\Eloquent\Builder<Phase>
     */
    public function findPhaseById(int $id): ?Phase
    {
        return $this->phaseRepository->findById($id);
    }

    /**
     * Summary of createPhase
     * @param array $data
     * @return Phase
     */
    public function createPhase(array $data): Phase
    {
        $data['created_by'] = Auth::id();
        return $this->phaseRepository->create($data);
    }


    /**
     * Summary of updatePhase
     * @param \App\Models\Phase $phase
     * @param array $data
     * @return Phase|null
     */
    public function updatePhase(Phase $phase, array $data): Phase
    {
        $this->phaseRepository->update($phase, $data);
        return $phase->fresh();
    }

    /**
     * Summary of deletePhase
     * @param \App\Models\Phase $phase
     * @return bool|null
     */
    public function deletePhase(Phase $phase): ?bool
    {
        return $this->phaseRepository->delete($phase);
    }

    /**
     * Summary of togglePhaseActivation
     * @param array $ids
     * @return array{id: mixed, message: string, success: bool[]}
     */
    public function togglePhaseActivation(array $ids): array
    {
        return $this->phaseRepository->toggleActivation($ids);
    }
    public function getPaginatedPhasesByTag(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->phaseRepository->getPaginatedByTag($filters, $perPage);
    }
}
