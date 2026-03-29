<?php

namespace App\Repositories;

use App\Models\EvaluationGridCriteriaOperationModel;
use Illuminate\Database\Eloquent\Collection;

class EvaluationGridCriteriaOperationRepository
{
    public function all(): Collection
    {
        return EvaluationGridCriteriaOperationModel::all();
    }

    public function insertMultiple(array $data): bool
    {
        return EvaluationGridCriteriaOperationModel::insert($data);
    }

    public function findByEvaluationId(int $evaluationId): Collection
    {
        return EvaluationGridCriteriaOperationModel::where('evaluation_id', $evaluationId)->get();
    }
}
