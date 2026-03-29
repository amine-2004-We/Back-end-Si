<?php

namespace App\Services;

use App\Models\EvaluationGridModel;
use App\Repositories\EvaluationGridRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class EvaluationGridService
{
    public function __construct(public EvaluationGridRepository $evaluationGridRepository){}

    /**
     * Get paginated list of Evaluation Grids with filters from request.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only(['is_active', 'title', 'per_page']);
        return $this->evaluationGridRepository->all($filters);
    }

    /**
     * Get all evaluation grids without pagination.
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return $this->evaluationGridRepository->getAllWithoutPagination();
    }

    /**
     * Get all evaluation grids with only id and title.
     *
     * @return Collection
     */
    public function allTitles(): Collection
    {
        return $this->evaluationGridRepository->allTitles();
    }

    /**
     * Find an evaluation grid by ID.
     *
     * @param int $id
     * @return EvaluationGridModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationGridModel
    {
        return $this->evaluationGridRepository->find($id);
    }

    /**
     * Create a new evaluation grid.
     *
     * @param array $data
     * @return EvaluationGridModel
     */
    public function create(array $data): EvaluationGridModel
    {
        return $this->evaluationGridRepository->create($data);
    }

    /**
     * Update an evaluation grid by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationGridModel
     */
    public function update(int $id, array $data): EvaluationGridModel
    {
        return $this->evaluationGridRepository->update($id, $data);
    }

    /**
     * Delete an evaluation grid by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->evaluationGridRepository->delete($id);
    }

    /**
     * Bulk delete evaluation grids by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->evaluationGridRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted evaluation grid by ID.
     *
     * @param int $id
     * @return EvaluationGridModel
     */
    public function restore(int $id): EvaluationGridModel
    {
        try {
            return $this->evaluationGridRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
