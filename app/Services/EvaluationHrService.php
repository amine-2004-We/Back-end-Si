<?php

namespace App\Services;

use App\Models\EvaluationHr;
use App\Repositories\EvaluationsHrRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;

class EvaluationHrService
{
    /** @var EvaluationsHrRepository */
    protected EvaluationsHrRepository $evaluationHrRepository;

    /**
     * @param EvaluationsHrRepository $evaluationHrRepository
     */
    public function __construct(EvaluationsHrRepository $evaluationHrRepository)
    {
        $this->evaluationHrRepository = $evaluationHrRepository;
    }

    /**
     * Get all EvaluationHr records with filters and pagination.
     *
     * @param Request $request
     * @return PaginationLengthAwarePaginator
     */
    public function getAll(Request $request): PaginationLengthAwarePaginator
    {
        $filters = $request->only([
            'is_active',
            'collaborator_id',
            'per_page',
        ]);

        return $this->evaluationHrRepository->all($filters);
    }

    /**
     * Get all EvaluationHr records with selected fields.
     *
     * @return Collection
     */
    public function allTypes(): Collection
    {
        return $this->evaluationHrRepository->allTypes();
    }

    /**
     * Find a single EvaluationHr by ID.
     *
     * @param int $id
     * @return EvaluationHr
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationHr
    {
        return $this->evaluationHrRepository->find($id);
    }

    /**
     * Create a new EvaluationHr.
     *
     * @param array $data
     * @return EvaluationHr
     */
    public function create(array $data): EvaluationHr
    {
        return $this->evaluationHrRepository->create($data);
    }

    /**
     * Update an EvaluationHr by ID.
     *
     * @param int $id
     * @param array $data
     * @return EvaluationHr
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EvaluationHr
    {
        return $this->evaluationHrRepository->update($id, $data);
    }

    /**
     * Delete an EvaluationHr by ID.
     *
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        return $this->evaluationHrRepository->delete($id);
    }

    /**
     * Bulk delete multiple EvaluationHr records by IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->evaluationHrRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted EvaluationHr by ID.
     *
     * @param int $id
     * @return EvaluationHr
     * @throws ModelNotFoundException
     */
    public function restore(int $id): EvaluationHr
    {
        try {
            return $this->evaluationHrRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
