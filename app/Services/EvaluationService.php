<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\Evaluation;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use App\Repositories\EvaluationGridCriteriaRepository;
use App\Repositories\EvaluationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EvaluationService
{
    public function __construct(
        protected EvaluationRepository $evaluationRepository,
        protected EvaluationGridCriteriaRepository $evaluationGridCriteriaRepository
    ) {}

    public function getAllEvaluations(array $filters = []): LengthAwarePaginator
    {
        return $this->evaluationRepository
            ->allWithFilters($filters)
            ->paginate(config('app.pagination_limit'));
    }

    public function getAllEvaluationsWithoutPagination(): Collection
    {
        return $this->evaluationRepository->allWithoutPagination();
    }

    public function getEvaluationById(int $id): Evaluation
    {
        return $this->evaluationRepository->find($id);
    }

    public function getEvaluationDetails(int $id): Evaluation
    {
        return $this->evaluationRepository->findWithDetails($id);
    }

    public function createEvaluation(
        array $data,
        User $createdBy,
        Collaborator $evaluator,
        ?Project $project,
        ?Partner $partner,
        ?array $criteriaScores
    ): Evaluation {
        return $this->evaluationRepository->createWithAssociations(
            $data,
            $createdBy,
            $evaluator,
            $project,
            $partner,
            $criteriaScores
        );
    }

    public function updateEvaluation(
        int $id,
        array $data,
        ?Collaborator $evaluator,
        ?Project $project,
        ?Partner $partner,
        ?array $criteriaScores
    ): Evaluation {
        return $this->evaluationRepository->update(
            $id,
            $data,
            $evaluator,
            $project,
            $partner,
            $criteriaScores
        );
    }

    public function deleteEvaluation(int $id): void
    {
        $this->evaluationRepository->delete($id);
    }

    public function bulkDeleteEvaluations(array $ids): void
    {
        $this->evaluationRepository->bulkDelete($ids);
    }

    public function restoreEvaluation(int $id): Evaluation
    {
        return $this->evaluationRepository->restoreEvaluation($id);
    }

    public function calculateEvaluationScore(int $evaluationId): float
    {
        return $this->evaluationRepository->calculateTotalScore($evaluationId);
    }
    public function getCriteriaWithEvaluationId(int $id)
    {
        return $this->evaluationGridCriteriaRepository->findByEvaluationId($id);
    }
}
