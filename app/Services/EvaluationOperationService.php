<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\EvaluationOperation;
use App\Models\TaskEvaluation;
use App\Models\User;
use App\Repositories\EvaluationGridCriteriaOperationRepository;
use App\Repositories\EvaluationOperationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EvaluationOperationService
{
    public function __construct(
        protected EvaluationOperationRepository $evaluationOperationRepository,
        protected EvaluationGridCriteriaOperationRepository $evaluationGridCriteriaOperationRepository
    ) {}

    public function getAllEvaluations(array $filters = []): LengthAwarePaginator
    {
        return $this->evaluationOperationRepository
            ->allWithFilters($filters)
            ->paginate(config('app.pagination_limit'));
    }

    public function getAllEvaluationsWithoutPagination(): Collection
    {
        return $this->evaluationOperationRepository->allWithoutPagination();
    }

    public function getEvaluationById(int $id): EvaluationOperation
    {
        return $this->evaluationOperationRepository->find($id);
    }

    public function getEvaluationDetails(int $id): EvaluationOperation
    {
        return $this->evaluationOperationRepository->findWithDetails($id);
    }

    public function createEvaluation(
        array $data,
        User $createdBy,
        Collaborator $evaluator,
        ?TaskEvaluation $session,
        ?array $criteriaScores
    ): EvaluationOperation {
        return $this->evaluationOperationRepository->createWithAssociations(
            $data,
            $createdBy,
            $evaluator,
            $session,
            $criteriaScores
        );
    }

    public function updateEvaluation(
        int $id,
        array $data,
        ?Collaborator $evaluator,
        ? TaskEvaluation $session,
        ?array $criteriaScores
    ): EvaluationOperation {
        return $this->evaluationOperationRepository->update(
            $id,
            $data,
            $evaluator,
            $session,
            $criteriaScores
        );
    }

    public function deleteEvaluation(int $id): void
    {
        $this->evaluationOperationRepository->delete($id);
    }

    public function bulkDeleteEvaluations(array $ids): void
    {
        $this->evaluationOperationRepository->bulkDelete($ids);
    }

    public function restoreEvaluation(int $id): EvaluationOperation
    {
        return $this->evaluationOperationRepository->restoreEvaluation($id);
    }

    public function calculateEvaluationScore(int $evaluationId): float
    {
        return $this->evaluationOperationRepository->calculateTotalScore($evaluationId);
    }

    public function getCriteriaWithEvaluationId(int $id)
    {
        return $this->evaluationGridCriteriaOperationRepository->findByEvaluationId($id);
    }
}
