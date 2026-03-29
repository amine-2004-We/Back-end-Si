<?php

namespace App\Repositories;

use App\Models\EvaluationGridModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

class EvaluationGridRepository
{
    /**
     * Get paginated list of Evaluation Grids with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = EvaluationGridModel::query()
            ->orderBy('created_at', 'desc')
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
        return EvaluationGridModel::all('id', 'title');
    }

    /**
     * Find an Evaluation Grid by ID, including soft deleted.
     *
     * @param int $id
     * @return EvaluationGridModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationGridModel
    {
        return EvaluationGridModel::withTrashed()->findOrFail($id);
    }

    /**
     * Get all evaluation grids without pagination (id and title).
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return EvaluationGridModel::all('id', 'title');
    }

    /**
     * Create a new Evaluation Grid.
     *
     * @param array $data
     * @return EvaluationGridModel
     */
    public function create(array $data): EvaluationGridModel
    {
        return EvaluationGridModel::create($data);
    }

    /**
     * Update an Evaluation Grid by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationGridModel
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EvaluationGridModel
    {
        $grid = $this->find($id);
        $grid->update($data);
        return $grid;
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
        $grid = $this->find($id);
        return $grid->delete();
    }

    /**
     * Bulk delete Evaluation Grids by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return EvaluationGridModel::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft deleted Evaluation Grid by ID.
     *
     * @param int $id
     * @return EvaluationGridModel
     */
    public function restore(int $id): EvaluationGridModel
    {
        $grid = EvaluationGridModel::onlyTrashed()->findOrFail($id);

        $exists = EvaluationGridModel::where('grid_code', $grid->grid_code)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'Une grille avec ce code existe déjà');
        }

        $grid->restore();

        return $grid;
    }
}
