<?php

namespace App\Services;

use App\Enums\GridStatusEnum;
use App\Enums\NiveauAppreciationEnum;
use App\Models\EvaluationGridOperation;
use App\Models\Project;
use App\Models\Program;
use App\Models\TaskEvaluation;
use App\Models\User;
use App\Repositories\EvaluationGridOperationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class EvaluationGridOperationService
{
    public function __construct(
        protected EvaluationGridOperationRepository $evaluationGridOperationRepository
    ) {}

    /**
     * Get all evaluation grid operations with optional filters
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllGridOperations(array $filters = []): LengthAwarePaginator
    {
        return $this->evaluationGridOperationRepository
            ->allWithFilters($filters)
            ->paginate(config('app.pagination_limit'));
    }

    /**
     * Get all evaluation grid operations without pagination
     * @return Collection
     */
    public function getAllGridOperationsWithoutPagination(): Collection
    {
        return $this->evaluationGridOperationRepository->allWithoutPagination();
    }

    /**
     * Get evaluation grid operation by ID
     * @param int $id
     * @return EvaluationGridOperation
     * @throws ModelNotFoundException
     */
    public function getGridOperationById(int $id): EvaluationGridOperation
    {
        return $this->evaluationGridOperationRepository->find($id);
    }

    /**
     * Get evaluation grid operation with detailed relationships
     * @param int $id
     * @return EvaluationGridOperation
     * @throws ModelNotFoundException
     */
    public function getGridOperationDetails(int $id): EvaluationGridOperation
    {
        return $this->evaluationGridOperationRepository->findWithDetails($id);
    }

    /**
     * Create a new evaluation grid operation with associations
     * @param array $data
     * @param TaskEvaluation|null $taskEvaluation
     * @param Project|null $project
     * @param Program|null $program
     * @param User|null $user
     * @return EvaluationGridOperation
     */
    public function createGridOperation(
        array $data,
        ?TaskEvaluation $taskEvaluation,
        ?Project $project,
        ?Program $program,
        ?User $user
    ): EvaluationGridOperation {
        return $this->evaluationGridOperationRepository->createWithAssociations(
            $data,
            $taskEvaluation,
            $project,
            $program,
            $user
        );
    }

    /**
     * Update an existing evaluation grid operation
     * @param int $id
     * @param array $data
     * @param TaskEvaluation|null $taskEvaluation
     * @param Project|null $project
     * @param Program|null $program
     * @param User|null $user
     * @return EvaluationGridOperation
     * @throws \Exception
     */
    public function updateGridOperation(
        int $id,
        array $data,
        ?TaskEvaluation $taskEvaluation,
        ?Project $project,
        ?Program $program,
        ?User $user
    ): EvaluationGridOperation {
        return $this->evaluationGridOperationRepository->update(
            $id,
            $data,
            $taskEvaluation,
            $project,
            $program,
            $user
        );
    }

    /**
     * Delete an evaluation grid operation
     * @param int $id
     * @return void
     * @throws ModelNotFoundException
     */
    public function deleteGridOperation(int $id): void
    {
        $this->evaluationGridOperationRepository->delete($id);
    }

    /**
     * Delete multiple evaluation grid operations in bulk
     * @param array $ids
     * @return void
     */
    public function bulkDeleteGridOperations(array $ids): void
    {
        $this->evaluationGridOperationRepository->bulkDelete($ids);
    }

    /**
     * Restore a soft-deleted evaluation grid operation
     * @param int $id
     * @return EvaluationGridOperation
     * @throws ModelNotFoundException
     */
    public function restoreGridOperation(int $id): EvaluationGridOperation
    {
        return $this->evaluationGridOperationRepository->restore($id);
    }

    /**
     * Get evaluation grid operations by status
     * @param GridStatusEnum $status
     * @return Collection
     */
    public function getGridOperationsByStatus(GridStatusEnum $status): Collection
    {
        return $this->evaluationGridOperationRepository->getByStatus($status);
    }

    /**
     * Get evaluation grid operations by niveau appreciation
     * @param NiveauAppreciationEnum $niveau
     * @return Collection
     */
    public function getGridOperationsByNiveauAppreciation(NiveauAppreciationEnum $niveau): Collection
    {
        return $this->evaluationGridOperationRepository->getByNiveauAppreciation($niveau);
    }

    /**
     * Get evaluation grid operations by evaluation code
     * @param string $evaluationCode
     * @return Collection
     */
    public function getGridOperationsByEvaluationCode(string $evaluationCode): Collection
    {
        return $this->evaluationGridOperationRepository->getByEvaluationCode($evaluationCode);
    }

    /**
     * Get the latest version of a grid by grid code
     * @param string $gridCode
     * @return EvaluationGridOperation|null
     */
    public function getLatestGridVersion(string $gridCode): ?EvaluationGridOperation
    {
        return $this->evaluationGridOperationRepository->getLatestVersion($gridCode);
    }
}
