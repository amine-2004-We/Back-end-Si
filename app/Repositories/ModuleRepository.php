<?php

namespace App\Repositories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class ModuleRepository
 */
class ModuleRepository
{
    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function withFilters(array $filters): LengthAwarePaginator
    {
        $query = Module::query()
            ->with(['training', 'trainer.internalTrainer.collaborator','trainer.externalTrainer','trainer', 'competencyGrid', 'creator','sessions','sessions.site','sessions.trainingGroup'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['training_id'])) {
            $query->where('training_id', $filters['training_id']);
        }

        if (!empty($filters['trainer_id'])) {
            $query->where('trainer_id', $filters['trainer_id']);
        }

        if (!empty($filters['competency_grid_id'])) {
            $query->where('competency_grid_id', $filters['competency_grid_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Module
     * @throws ModelNotFoundException
     */
    public function find(int $id): Module
    {
        return Module::with(['training', 'trainer', 'competencyGrid', 'creator'])->findOrFail($id);
    }

    /**
     * @param int $id
     * @return Module
     * @throws ModelNotFoundException
     */
    public function findWithTrashed(int $id): Module
    {
        return Module::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Module
     */
    public function create(array $data): Module
    {
        return Module::create($data);
    }

    /**
     * @param array $data
     * @param int $id
     * @return Module
     */
    public function update(array $data, int $id): Module
    {
        $module = $this->find($id);
        $module->update($data);
        return $module;
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id): ?bool
    {
        $module = $this->find($id);
        return $module->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Module::destroy($ids);
    }

    /**
     * @param int $id
     * @return Module
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Module
    {
        $module = $this->findWithTrashed($id);
        $module->restore();
        return $module;
    }
}
