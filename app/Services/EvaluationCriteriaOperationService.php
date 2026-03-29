<?php

namespace App\Services;

use App\Models\EvaluationCriteriaModel;
use App\Models\EvaluationCriteriaOperationModal;
use App\Repositories\EvaluationCriteriaOperationRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;
class EvaluationCriteriaOperationService
{
    protected EvaluationCriteriaOperationRepository $evaluationCriteriaRepository;

    public function __construct(EvaluationCriteriaOperationRepository $evaluationCriteriaRepository)
    {
        $this->evaluationCriteriaRepository = $evaluationCriteriaRepository;
    }

    /**
     * Get paginated list of Evaluation criteria with filters from request.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only(['is_active', 'name', 'per_page']);
        return $this->evaluationCriteriaRepository->all($filters);
    }

    /**
     * Get all evaluation criteria without pagination.
     *
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return $this->evaluationCriteriaRepository->getAllWithoutPagination();
    }

    /**
     * Get all evaluation criteria with only id and title.
     *
     * @return Collection
     */
    public function allTitles(): Collection
    {
        return $this->evaluationCriteriaRepository->allTitles();
    }

    /**
     * Find an evaluation criteria by ID.
     *
     * @param int $id
     * @return EvaluationCriteriaModel
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationCriteriaOperationModal
    {
        return $this->evaluationCriteriaRepository->find($id);
    }

    /**
     * Create a new evaluation criteria.
     *
     * @param array $data
     * @return EvaluationCriteriaOperationModal
     */
    public function create(array $data): EvaluationCriteriaOperationModal
    {
        return $this->evaluationCriteriaRepository->create($data);
    }

    /**
     * Update an evaluation criteria by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationCriteriaOperationModal
     */
    public function update(int $id, array $data): EvaluationCriteriaOperationModal
    {
        return $this->evaluationCriteriaRepository->update($id, $data);
    }

    /**
     * Delete an evaluation criteria by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->evaluationCriteriaRepository->delete($id);
    }

    /**
     * Bulk delete evaluation criteria by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->evaluationCriteriaRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted evaluation criteria by ID.
     *
     * @param int $id
     * @return EvaluationCriteriaModel
     */
    public function restore(int $id): EvaluationCriteriaOperationModal
    {
        try {
            return $this->evaluationCriteriaRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
