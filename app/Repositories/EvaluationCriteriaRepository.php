<?php

namespace App\Repositories;

use App\Models\EvaluationCriteriaModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class EvaluationCriteriaRepository
{

    /**
     * Get paginated list of Evaluation Grids with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = EvaluationCriteriaModel::query()
            ->with('creator')
            ->orderBy('order')
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
        return EvaluationCriteriaModel::all('id', 'name','evaluation_grid_id');
    }

    /**
     * Find an Evaluation Grid by ID, including soft deleted.
     *
     * @param int $id
     * @return EvaluationCriteriaModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationCriteriaModel
    {
        return EvaluationCriteriaModel::withTrashed()
            ->with('creator')
            ->findOrFail($id);
    }

    /**
     * Get all evaluation grids without pagination (id and title).
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return EvaluationCriteriaModel::all('id', 'name');
    }

    /**
     * Create a new Evaluation Grid.
     *
     * @param array $data
     * @return EvaluationCriteriaModel
     */
    public function create(array $data): EvaluationCriteriaModel
    {
        $criteria = EvaluationCriteriaModel::create($data);
        return $criteria->load('creator');
    }

    /**
     * Update an Evaluation Grid by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationCriteriaModel
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EvaluationCriteriaModel
    {
        $criteria = $this->find($id);
        $criteria->update($data);
        return $criteria->load('creator');
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
        return EvaluationCriteriaModel::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Evaluation Grid by ID.
     *
     * @param int $id
     * @return EvaluationCriteriaModel
     */
    public function restore(int $id): EvaluationCriteriaModel
    {
        $criteria = EvaluationCriteriaModel::onlyTrashed()->findOrFail($id);

        $exists = EvaluationCriteriaModel::where('id', $criteria->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'Un critère  avec ce code existe déjà');
        }

        $criteria->restore();

        return $criteria->load('creator');
    }
}
