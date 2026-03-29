<?php

namespace App\Services;

use App\Models\CompetencyCriterion;
use App\Repositories\CompetencyCriterionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 *class CompetencyCriterionService
 */
class CompetencyCriterionService
{
    /**
     * @param CompetencyCriterionRepository $repository
     */
    public function __construct(protected CompetencyCriterionRepository $repository)
    {
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * @param int $id
     * @return CompetencyCriterion|null
     */
    public function findById(int $id): ?CompetencyCriterion
    {
        return $this->repository->findById($id);
    }

    /**
     * @param array $data
     * @return CompetencyCriterion
     */
    public function create(array $data): CompetencyCriterion
    {
        return DB::transaction(function () use ($data) {
            $criterion = $this->repository->create($data);
            return $criterion->load($this->repository->defaultWith);
        });
    }

    /**
     * @param CompetencyCriterion $criterion
     * @param array $data
     * @return CompetencyCriterion
     */
    public function update(CompetencyCriterion $criterion, array $data): CompetencyCriterion
    {
        return DB::transaction(function () use ($criterion, $data) {
            $this->repository->update($criterion, $data);
            return $criterion->fresh($this->repository->defaultWith);
        });
    }

    /**
     * @param CompetencyCriterion $criterion
     * @return bool
     */
    public function delete(CompetencyCriterion $criterion): bool
    {
        return DB::transaction(function () use ($criterion) {
            return $this->repository->delete($criterion);
        });
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        return $this->repository->toggleActivation($ids);
    }
}
