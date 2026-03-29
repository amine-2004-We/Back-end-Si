<?php

namespace App\Repositories;

use App\Models\CompetencyGrid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompetencyGridRepository
{
    /**
     * Filtrer et paginer les grilles de compétences
     */
    public function withFilters(array $filters = []): LengthAwarePaginator
    {
        $query = CompetencyGrid::query()
            ->with(['creator', 'criteria','modules'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['grid_type'])) {
            $query->where('grid_type', $filters['grid_type']);
        }

        if (!empty($filters['grading_scheme'])) {
            $query->where('grading_scheme', $filters['grading_scheme']);
        }

        if (!empty($filters['lifecycle_status'])) {
            $query->where('lifecycle_status', $filters['lifecycle_status']);
        }

        if (!empty($filters['created_by_id'])) {
            $query->where('created_by_id', (int) $filters['created_by_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('code', 'ilike', "%{$s}%")
                  ->orWhere('title', 'ilike', "%{$s}%")
                  ->orWhere('pedagogical_objective', 'ilike', "%{$s}%");
            });
        }

        if (!empty($filters['code'])) {
            $query->where('code', 'ilike', '%' . trim($filters['code']) . '%');
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        $perPage = isset($filters['per_page']) ? max(1, (int) $filters['per_page']) : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, CompetencyGrid>
     */
    public function all(): Collection
    {
        return CompetencyGrid::with(['creator', 'criteria'])->get();
    }

    /**
     * @return Collection<int, CompetencyGrid>
     */
    public function allWithoutPagination(): Collection
    {
        return $this->all();
    }

    /**
     * @param int $id
     * @return CompetencyGrid
     */
    public function find(int $id): CompetencyGrid
    {
        return CompetencyGrid::findOrFail($id);
    }

    /**
     * @param int $id
     * @return CompetencyGrid
     */
    public function findWithTrashed(int $id): CompetencyGrid
    {
        return CompetencyGrid::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return CompetencyGrid
     */
    public function create(array $data): CompetencyGrid
    {
        return CompetencyGrid::create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return CompetencyGrid
     */
    public function update(array $data, int $id): CompetencyGrid
    {
        $grid = $this->find($id);
        $grid->update($data);
        return $grid;
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id): int
    {
        return $this->find($id)->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return CompetencyGrid::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return CompetencyGrid
     */
    public function restore(int $id): CompetencyGrid
    {
        $grid = $this->findWithTrashed($id);
        $grid->restore();

        return $grid;
    }

    /**
     * @param \App\Models\CompetencyGrid $grid
     * @param array $criteria
     * @return CompetencyGrid
     */
    public function syncCriteria(CompetencyGrid $grid, array $criteria): CompetencyGrid
    {
        DB::transaction(function () use ($grid, $criteria) {
            $grid->criteria()->detach();

            $syncData = [];

            $criteriaIds = [];

            foreach ($criteria as $criterion) {
                if (!empty($criterion['criterion_id'])) {
                    $criteriaIds[] = (int) $criterion['criterion_id'];
                }
            }

            if (!empty($criteriaIds)) {
                $grid->criteria()->sync($criteriaIds);
            }
        });

        return $grid->load('criteria');
    }
}
