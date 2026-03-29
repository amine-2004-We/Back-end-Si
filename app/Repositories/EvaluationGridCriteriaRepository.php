<?php

namespace App\Repositories;

use App\Models\EvaluationGridCriteriaModel;
use Illuminate\Database\Eloquent\Collection;

class EvaluationGridCriteriaRepository
{
    public function all():Collection
    {
        return EvaluationGridCriteriaModel::all();
    }
    public function insertMultiple(array $data): bool
    {
        return EvaluationGridCriteriaModel::insert($data);
    }
    public function findByEvaluationId(int $evaluationId)
    {
        return EvaluationGridCriteriaModel::where('evaluation_id', $evaluationId)
            ->get();
    }


}
