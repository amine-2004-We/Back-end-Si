<?php

namespace App\Repositories;

use App\Models\Collaborator;
use App\Models\EvaluationOperation;
use App\Models\EvaluationGridCriteriaOperationModel;
use App\Models\TaskEvaluation;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EvaluationOperationRepository
{
    protected EvaluationGridCriteriaOperationRepository $criteriaRepository;

    public function __construct(EvaluationGridCriteriaOperationRepository $criteriaRepository)
    {
        $this->criteriaRepository = $criteriaRepository;
    }

    /**
     * @return Builder
     */
    public function all(): Builder
    {
        return EvaluationOperation::query();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function allWithFilters(array $filters): Builder
    {
        $query = EvaluationOperation::query()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

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

        if (!empty($filters['evaluation_status_operation'])) {
            $query->where('evaluation_status_operation', $filters['evaluation_status_operation']);
        }

        if (!empty($filters['evaluation_code'])) {
            $query->where('evaluation_code', 'like', '%'.$filters['evaluation_code'].'%');
        }

        if (!empty($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        if (!empty($filters['evaluator_id'])) {
            $query->where('evaluator', $filters['evaluator_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return EvaluationOperation::all(['id', 'evaluation_code', 'evaluation_status_operation']);
    }

    /**
     * @param int $id
     * @return EvaluationOperation
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationOperation
    {
        return EvaluationOperation::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return EvaluationOperation
     */
    public function create(array $data): EvaluationOperation
    {
        return EvaluationOperation::create($data);
    }

    /**
     * @param array $data
     * @param User $creator
     * @param Collaborator $evaluator
     * @param TaskEvaluation|null $session
     * @param array|null $criteriaScores
     * @return EvaluationOperation
     */
    public function createWithAssociations(
        array $data,
        User $creator,
        Collaborator $evaluator,
        ?TaskEvaluation $session,
        ?array $criteriaScores
    ): EvaluationOperation {
        return DB::transaction(function () use (
            $data,
            $creator,
            $evaluator,
            $session,
            $criteriaScores
        ) {
            $cleanData = collect($data)->except('criteria_scores')->toArray();
            $evaluation = new EvaluationOperation($cleanData);
            $evaluation->creator()->associate($creator);
            $evaluation->evaluator()->associate($evaluator);

            if ($session) {
                $evaluation->session_id = $session->id;
            }

            $evaluation->save();

            if (!empty($criteriaScores)) {
                $this->syncCriteriaScores($evaluation, $criteriaScores);
            }

            return $evaluation;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @param Collaborator|null $evaluator
     * @param TaskEvaluation|null $session
     * @param array|null $criteriaScores
     * @return EvaluationOperation
     * @throws Exception
     */
    public function update(
        int $id,
        array $data,
        ?Collaborator $evaluator,
        ?TaskEvaluation $session,
        ?array $criteriaScores
    ): EvaluationOperation {
        try {
            $evaluation = $this->find($id);
            $cleanData = collect($data)->except('criteria_scores')->toArray();
            $evaluation->update($cleanData);

            if ($evaluator) {
                $evaluation->evaluator()->associate($evaluator);
            }

            $evaluation->session_id = $session?->id;
            $evaluation->save();

            if (!empty($criteriaScores)) {
                $this->syncCriteriaScores($evaluation, $criteriaScores);
            }

            return $evaluation;
        } catch (Exception $e) {
            throw new Exception('Error updating evaluation operation: ' . $e->getMessage());
        }
    }

    /**
     * Sync the evaluation criteria scores using the repository's insertMultiple method
     *
     * @param EvaluationOperation $evaluation
     * @param array $criteriaScores
     * @return void
     */
    protected function syncCriteriaScores(EvaluationOperation $evaluation, array $criteriaScores): void
    {
        $syncData = [];

        foreach ($criteriaScores as $scoreData) {
            if (!empty($scoreData['criteria_id']) && !empty($scoreData['grid_id'])) {
                $syncData[] = [
                    'evaluation_id' => $evaluation->id,
                    'grid_id' => $scoreData['grid_id'],
                    'criteria_id' => $scoreData['criteria_id'],
                    'score' => $scoreData['score'] ?? 0,
                ];
            }
        }

        try {
            // Delete existing scores for this evaluation
            EvaluationGridCriteriaOperationModel::where('evaluation_id', $evaluation->id)->delete();

            if (!empty($syncData)) {
                // Use repository to insert
                $this->criteriaRepository->insertMultiple($syncData);
            }
        } catch (Exception $e) {
            logger()->error('Error syncing operation criteria scores: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $evaluation = $this->find($id);
        return $evaluation->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return EvaluationOperation::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return EvaluationOperation
     */
    public function restoreEvaluation(int $id): EvaluationOperation
    {
        $evaluation = $this->find($id);
        $evaluation->restore();
        return $evaluation;
    }

    /**
     * @param int $id
     * @return EvaluationOperation
     * @throws ModelNotFoundException
     */
    public function findWithDetails(int $id): EvaluationOperation
    {
        return EvaluationOperation::with([
            'creator',
            'evaluator',
            'criteriaScores.grid',
            'criteriaScores.criteria'
        ])->findOrFail($id);
    }

    /**
     * Calculate the total score for an evaluation
     *
     * @param int $evaluationId
     * @return float
     */
    public function calculateTotalScore(int $evaluationId): float
    {
        return EvaluationGridCriteriaOperationModel::where('evaluation_id', $evaluationId)->sum('score');
    }
}
