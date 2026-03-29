<?php

namespace App\Repositories;

use App\Models\InternalTrainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class InternalTrainerRepository
 */
class InternalTrainerRepository
{
    /**
     * @param array $filters
     * @return Builder
     */
    public function withFilters(array $filters): Builder
    {
        $query = InternalTrainer::query()
            ->with([
                'collaborator.department',
                'collaborator.position',
                'trainer',
                'trainer.createdBy',
                'collaborator.assignedRegion',
                'trainer.modules',
                'trainer.modules.training'
            ])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['collaborator_id'])) {
            $query->where('collaborator_id', $filters['collaborator_id']);
        }

        if (!empty($filters['trainer_id'])) {
            $query->where('trainer_id', $filters['trainer_id']);
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

        return $query;
    }

    /**
     * @param int $id
     * @return InternalTrainer
     * @throws ModelNotFoundException
     */
    public function find(int $id): InternalTrainer
    {
        return InternalTrainer::with(['collaborator', 'trainer'])->findOrFail($id);
    }

    /**
     * @param int $id
     * @return InternalTrainer
     * @throws ModelNotFoundException
     */
    public function findWithTrashed(int $id): InternalTrainer
    {
        return InternalTrainer::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return InternalTrainer
     */
    public function create(array $data): InternalTrainer
    {
        $internalTrainer = new InternalTrainer();
        $internalTrainer->fill($data);
        $internalTrainer->save();
        return $internalTrainer;
    }

    /**
     * @param array $data
     * @param int $id
     * @return InternalTrainer
     */
    public function update(array $data, int $id): InternalTrainer
    {
        $internalTrainer = $this->find($id);
        $internalTrainer->update($data);
        return $internalTrainer;
    }

    /**
     * @param int $id
     * @return InternalTrainer
     */
    public function delete(int $id): InternalTrainer
    {
        $internalTrainer = $this->find($id);
        $internalTrainer->delete();
        return $internalTrainer;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return InternalTrainer::destroy($ids);
    }

    /**
     * @param int $id
     * @return InternalTrainer
     * @throws ModelNotFoundException
     */
    public function restore(int $id): InternalTrainer
    {
        $internalTrainer = $this->findWithTrashed($id);

        if (!$internalTrainer) {
            abort(404, 'Internal trainer not found.');
        }

        // Prevent restoring if an active record for the same collaborator already exists
        $exists = InternalTrainer::where('collaborator_id', $internalTrainer->collaborator_id)
                               ->whereNull('deleted_at')
                               ->exists();

        if ($exists) {
            abort(409, 'Ce collaborateur est déjà désigné comme formateur interne actif.');
        }

        $internalTrainer->restore();
        return $internalTrainer;
    }
}
