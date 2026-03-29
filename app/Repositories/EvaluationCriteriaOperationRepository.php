<?php

namespace App\Repositories;

use App\Models\EvaluationCriteriaOperationModal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class EvaluationCriteriaOperationRepository
{

    /**
     * Get paginated list of Evaluation Grids with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = EvaluationCriteriaOperationModal::query()
            ->orderByRaw('deleted_at IS NOT NULL');

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

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * Get all evaluation grids with only id and title.
     *
     * @return Collection
     */
    public function allTitles(): Collection
    {
        return EvaluationCriteriaOperationModal::all('id', 'title','grid_evaluation_id');
    }

    /**
     * Find an Evaluation Grid by ID, including soft deleted.
     *
     * @param int $id
     * @return EvaluationCriteriaOperationModal
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationCriteriaOperationModal
    {
        return EvaluationCriteriaOperationModal::withTrashed()->findOrFail($id);
    }

    /**
     * Get all evaluation grids without pagination (id and title).
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return EvaluationCriteriaOperationModal::all('id', 'name');
    }

    /**
     * Create a new Evaluation Grid.
     *
     * @param array $data
     * @return EvaluationCriteriaOperationModal
     */
    public function create(array $data): EvaluationCriteriaOperationModal
    {
        return EvaluationCriteriaOperationModal::create($data);
    }

    /**
     * Update an Evaluation Grid by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationCriteriaOperationModal
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EvaluationCriteriaOperationModal
    {
        $criteria = $this->find($id);
        $criteria->update($data);
        return $criteria;
    }

    /**
     * Delete an Evaluation Grid by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $criteria = $this->find($id);
        return $criteria->delete();
    }

    /**
     * Bulk delete Evaluation Grids by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return EvaluationCriteriaOperationModal::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Evaluation Grid by ID.
     *
     * @param int $id
     * @return EvaluationCriteriaOperationModal
     */
    public function restore(int $id): EvaluationCriteriaOperationModal
    {
        $criteria = EvaluationCriteriaOperationModal::onlyTrashed()->findOrFail($id);

        $exists = EvaluationCriteriaOperationModal::where('id', $criteria->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'Un critère  avec ce code existe déjà');
        }

        $criteria->restore();

        return $criteria;
    }
}
