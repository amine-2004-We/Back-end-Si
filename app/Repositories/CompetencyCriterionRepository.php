<?php

namespace App\Repositories;

use App\Models\CompetencyCriterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *class CompetencyCriterionRepository
 */
class CompetencyCriterionRepository
{
    /**
     * @var array|string[]
     */
    public array $defaultWith = ['creator', 'competencyGrids'];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CompetencyCriterion::query()->with($this->defaultWith)->withTrashed();

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('identifier', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($perPage);
    }

    /**
     * @param int $id
     * @return CompetencyCriterion|null
     */
    public function findById(int $id): ?CompetencyCriterion
    {
        return CompetencyCriterion::withTrashed()->with($this->defaultWith)->find($id);
    }

    /**
     * @param array $data
     * @return CompetencyCriterion
     */
    public function create(array $data): CompetencyCriterion
    {
        return CompetencyCriterion::create($data);
    }

    /**
     * @param CompetencyCriterion $criterion
     * @param array $data
     * @return bool
     */
    public function update(CompetencyCriterion $criterion, array $data): bool
    {
        return $criterion->update($data);
    }

    /**
     * @param CompetencyCriterion $criterion
     * @return bool|null
     */
    public function delete(CompetencyCriterion $criterion): ?bool
    {
        return $criterion->delete();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        $criteria = CompetencyCriterion::withTrashed()->whereIn('id', $ids)->get();

        foreach ($criteria as $criterion) {
            $criterion->is_active = !$criterion->is_active;
            $criterion->save();
            $results[] = ['id' => $criterion->id, 'success' => true, 'is_active' => $criterion->is_active];
        }
        return $results;
    }
}
