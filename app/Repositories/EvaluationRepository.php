<?php

namespace App\Repositories;

use App\Models\Collaborator;
use App\Models\Evaluation;
use App\Models\EvaluationGridCriteriaModel;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class EvaluationRepository
{
    protected EvaluationGridCriteriaRepository $evaluationGridCriteriaRepository;
    public function __construct(EvaluationGridCriteriaRepository $evaluationGridCriteriaRepository)
    {
        $this->evaluationGridCriteriaRepository = $evaluationGridCriteriaRepository;
    }

    /**
     * @return Builder
     */
    public function all(): Builder
    {
        return Evaluation::query();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function allWithFilters(array $filters): Builder
    {
        $query = Evaluation::query()
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

        if (!empty($filters['evaluation_type'])) {
            $query->where('evaluation_type', $filters['evaluation_type']);
        }

        if (!empty($filters['evaluation_status'])) {
            $query->where('evaluation_status', $filters['evaluation_status']);
        }

        if (!empty($filters['evaluation_code'])) {
            $query->where('evaluation_code', 'like', '%'.$filters['evaluation_code'].'%');
        }

        if (!empty($filters['project_id'])) {
            $query->where('object_project', $filters['project_id']);
        }

        if (!empty($filters['partner_id'])) {
            $query->where('object_partner', $filters['partner_id']);
        }

        if (!empty($filters['evaluator_id'])) {
            $query->where('evaluator_id', $filters['evaluator_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('evaluation_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('evaluation_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Evaluation::all(['id', 'evaluation_code', 'evaluation_type']);
    }

    /**
     * @param int $id
     * @return Evaluation
     * @throws ModelNotFoundException
     */
    public function find(int $id): Evaluation
    {
        return Evaluation::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Evaluation
     */
    public function create(array $data): Evaluation
    {
        return Evaluation::create($data);
    }

    /**
     * @param array $data
     * @param User $createdBy
     * @param Collaborator $evaluator
     * @param Project|null $project
     * @param Partner|null $partner
     * @param array|null $criteriaScores
     * @return Evaluation
     */

    public function createWithAssociations(
        array $data,
        User $createdBy,
        Collaborator $evaluator,
        ?Project $project,
        ?Partner $partner,
        ?array $criteriaScores
    ): Evaluation {
        return DB::transaction(function () use (
            $data,
            $createdBy,
            $evaluator,
            $project,
            $partner,
            $criteriaScores
        ) {
            $cleanData = collect($data)->except('criteria_scores')->toArray();
            $evaluation = new Evaluation($cleanData);
            $evaluation->creator()->associate($createdBy);
            $evaluation->evaluator()->associate($evaluator);

            if ($project) {
                $evaluation->project()->associate($project);
            }

            if ($partner) {
                $evaluation->partner()->associate($partner);
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
     * @param Project|null $project
     * @param Partner|null $partner
     * @param array|null $criteriaScores
     * @return Evaluation
     * @throws Exception
     */
    public function update(
        int $id,
        array $data,
        ?Collaborator $evaluator,
        ?Project $project,
        ?Partner $partner,
        ?array $criteriaScores
    ): Evaluation {
        try {
            $evaluation = $this->find($id);
            $cleanData = collect($data)->except('criteria_scores')->toArray();
            $evaluation->update($cleanData);

            if ($evaluator) {
                $evaluation->evaluator()->associate($evaluator);
            }

            if ($project) {
                $evaluation->project()->associate($project);
            } else {
                $evaluation->project()->dissociate();
            }

            if ($partner) {
                $evaluation->partner()->associate($partner);
            } else {
                $evaluation->partner()->dissociate();
            }
            if (!empty($criteriaScores)) {
                $this->syncCriteriaScores($evaluation, $criteriaScores);
            }

            $evaluation->save();

            return $evaluation;
        } catch (Exception $e) {
            throw new Exception('Error updating evaluation: '.$e->getMessage());
        }
    }

    /**
     * @param Evaluation $evaluation
     * @param array $criteriaScores
     * @return void
     */
    protected function syncCriteriaScores(Evaluation $evaluation, array $criteriaScores): void
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
            EvaluationGridCriteriaModel::where('evaluation_id', $evaluation->id)->delete();
            if (!empty($syncData)) {
                $this->evaluationGridCriteriaRepository->insertMultiple($syncData);
            }
        } catch (\Exception $e) {
            logger()->error('Error syncing criteria scores: ' . $e->getMessage());
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
        return Evaluation::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return Evaluation
     */
    public function restoreEvaluation(int $id): Evaluation
    {
        $evaluation = $this->find($id);
        $evaluation->restore();
        return $evaluation;
    }

    /**
     * @param int $id
     * @return Evaluation
     * @throws ModelNotFoundException
     */
    public function findWithDetails(int $id): Evaluation
    {
        return Evaluation::with([
            'project',
            'partner',
            'evaluator',
            'notificationReceiver',
            'creator',
            'criteria.grid',
            'criteria.criteria'
        ])->findOrFail($id);
    }

    /**
     * Calculate and update the total score for an evaluation
     * @param int $evaluationId
     * @return float
     */
    public function calculateTotalScore(int $evaluationId): float
    {
        $totalScore = EvaluationGridCriteriaModel::where('evaluation_id', $evaluationId)
            ->sum('score');
        return $totalScore;
    }
}
