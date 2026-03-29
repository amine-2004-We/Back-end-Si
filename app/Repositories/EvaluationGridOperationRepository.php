<?php

namespace App\Repositories;

use App\Enums\GridStatusEnum;
use App\Enums\NiveauAppreciationEnum;
use App\Models\EvaluationGridOperation;
use App\Models\Project;
use App\Models\Program;
use App\Models\TaskEvaluation;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EvaluationGridOperationRepository
{
    /**
     * @return Builder
     */
    public function all(): Builder
    {
        return EvaluationGridOperation::query();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function allWithFilters(array $filters): Builder
    {
        $query = EvaluationGridOperation::query()
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

        if (!empty($filters['grid_code'])) {
            $query->where('grid_code', 'like', '%'.$filters['grid_code'].'%');
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        if (!empty($filters['task_evaluation_id'])) {
            $query->where('task_evaluation_id', $filters['task_evaluation_id']);
        }

        if (!empty($filters['educational_area'])) {
            $query->where('educational_area', 'like', '%'.$filters['educational_area'].'%');
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (!empty($filters['niveau_appreciation'])) {
            $query->where('niveau_appreciation', $filters['niveau_appreciation']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['grid_status'])) {
            $query->where('grid_status', $filters['grid_status']);
        }

        if (!empty($filters['grid_version'])) {
            $query->where('grid_version', $filters['grid_version']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return EvaluationGridOperation::all([
            'id',
            'grid_code',
            'title',
            'educational_area',
            'targeted_overall_skill',
            'sub_skill',
            'program_id',
            'niveau_appreciation',
            'created_at',
            'updated_at'
        ]);
    }


    /**
     * @param int $id
     * @return EvaluationGridOperation
     * @throws ModelNotFoundException
     */
    public function find(int $id): EvaluationGridOperation
    {
        return EvaluationGridOperation::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return EvaluationGridOperation
     */
    public function create(array $data): EvaluationGridOperation
    {
        return EvaluationGridOperation::create($data);
    }

    /**
     * @param array $data
     * @param TaskEvaluation|null $taskEvaluation
     * @param Project|null $project
     * @param Program|null $program
     * @param User|null $user
     * @return EvaluationGridOperation
     */
    public function createWithAssociations(
        array $data,
        ?TaskEvaluation $taskEvaluation,
        ?Project $project,
        ?Program $program,
        ?User $user
    ): EvaluationGridOperation {
        return DB::transaction(function () use (
            $data,
            $taskEvaluation,
            $project,
            $program,
            $user
        ) {
            $gridOperation = new EvaluationGridOperation($data);

            if ($taskEvaluation) {
                $gridOperation->taskEvaluation()->associate($taskEvaluation);
            }

            if ($project) {
                $gridOperation->project()->associate($project);
            }

            if ($program) {
                $gridOperation->program()->associate($program);
            }

            if ($user) {
                $gridOperation->user()->associate($user);
            }

            $gridOperation->save();

            return $gridOperation;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @param TaskEvaluation|null $taskEvaluation
     * @param Project|null $project
     * @param Program|null $program
     * @param User|null $user
     * @return EvaluationGridOperation
     * @throws Exception
     */
    public function update(
        int $id,
        array $data,
        ?TaskEvaluation $taskEvaluation,
        ?Project $project,
        ?Program $program,
        ?User $user
    ): EvaluationGridOperation {
        try {
            $gridOperation = $this->find($id);
            $gridOperation->update($data);

            if ($taskEvaluation) {
                $gridOperation->taskEvaluation()->associate($taskEvaluation);
            } else {
                $gridOperation->taskEvaluation()->dissociate();
            }

            if ($project) {
                $gridOperation->project()->associate($project);
            } else {
                $gridOperation->project()->dissociate();
            }

            if ($program) {
                $gridOperation->program()->associate($program);
            } else {
                $gridOperation->program()->dissociate();
            }

            if ($user) {
                $gridOperation->user()->associate($user);
            } else {
                $gridOperation->user()->dissociate();
            }

            $gridOperation->save();

            return $gridOperation;
        } catch (Exception $e) {
            throw new Exception('Error updating evaluation grid operation: '.$e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $gridOperation = $this->find($id);
        return $gridOperation->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return EvaluationGridOperation::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return EvaluationGridOperation
     */
    public function restore(int $id): EvaluationGridOperation
    {
        $gridOperation = $this->find($id);
        $gridOperation->restore();
        return $gridOperation;
    }

    /**
     * @param int $id
     * @return EvaluationGridOperation
     * @throws ModelNotFoundException
     */
    public function findWithDetails(int $id): EvaluationGridOperation
    {
        return EvaluationGridOperation::with([
            'taskEvaluation',
            'project',
            'program',
            'user'
        ])->findOrFail($id);
    }

    /**
     * Get grids by status
     * @param GridStatusEnum $status
     * @return Collection
     */
    public function getByStatus(GridStatusEnum $status): Collection
    {
        return EvaluationGridOperation::where('grid_status', $status)
            ->get();
    }

    /**
     * Get grids by niveau appreciation
     * @param NiveauAppreciationEnum $niveau
     * @return Collection
     */
    public function getByNiveauAppreciation(NiveauAppreciationEnum $niveau): Collection
    {
        return EvaluationGridOperation::where('niveau_appreciation', $niveau)
            ->get();
    }

    /**
     * Get grids by evaluation code
     * @param string $evaluationCode
     * @return Collection
     */
    public function getByEvaluationCode(string $evaluationCode): Collection
    {
        return EvaluationGridOperation::where('evaluation_code', $evaluationCode)
            ->get();
    }

    /**
     * Get latest grid version for a specific grid code
     * @param string $gridCode
     * @return EvaluationGridOperation|null
     */
    public function getLatestVersion(string $gridCode): ?EvaluationGridOperation
    {
        return EvaluationGridOperation::where('grid_code', $gridCode)
            ->orderByDesc('grid_version')
            ->first();
    }
}
