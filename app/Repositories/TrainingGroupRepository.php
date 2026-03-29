<?php

namespace App\Repositories;

use App\Models\TrainingGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class TrainingGroupRepository
 */
class TrainingGroupRepository
{
    /**
     * @param array $filters
     */
    public function withFilters(array $filters)
    {
        $query = TrainingGroup::query()
            ->with([
                'training',
                'createdBy',
                'collaborators',
                'candidates',
                'externals',
            ])->withCount(['collaborators', 'candidates', 'externals'])
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        if (!empty($filters['training_id'])) {
            $query->where('training_id', '=', $filters['training_id']);
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

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return TrainingGroup::select('id', 'title', 'status')->get();
    }

    /**
     * @param int $id
     * @return TrainingGroup
     */
    public function find(int $id): TrainingGroup
    {
        return TrainingGroup::with([
            'training',
            'createdBy',
            'collaborators',
            'candidates',
            'externals'
        ])->findOrFail($id);
    }

    /**
     * @param int $id
     * @return TrainingGroup
     */
    public function findWithTrashed(int $id): TrainingGroup
    {
        return TrainingGroup::withTrashed()
            ->with([
                'training',
                'createdBy',
                'collaborators',
                'candidates',
                'externals'
            ])
            ->findOrFail($id);
    }

    /**
     * @param array $data
     * @return TrainingGroup
     */
    public function create(array $data): TrainingGroup
    {
        
        $group = new TrainingGroup();
        $group->fill($data);
        $group->save();
        return $group;
    }

    /**
     * @param array $data
     * @param int $id
     * @return TrainingGroup
     */
    public function update(array $data, int $id): TrainingGroup
    {
        $group = $this->find($id);
        $group->update($data);
        return $group;
    }

    /**
     * @param int $id
     * @return TrainingGroup
     */
    public function delete(int $id): TrainingGroup
    {
        $group = $this->find($id);
        $group->delete();
        return $group;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return TrainingGroup::destroy($ids);
    }

    /**
     * @param int $id
     * @return TrainingGroup
     */
    public function restore(int $id): TrainingGroup
    {
        $group = $this->findWithTrashed($id);

        $exists = TrainingGroup::where('title', $group->title)
            ->where('training_id', $group->training_id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(409, 'Un groupe actif avec le même titre existe déjà pour cette formation.');
        }

        $group->restore();
        return $group;
    }
}

