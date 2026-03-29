<?php

namespace App\Observers;

use App\Models\EvaluationCriteriaModel;
use Illuminate\Support\Facades\Auth;

class EvaluationCriteriaObserver
{
    public function creating(EvaluationCriteriaModel $evaluationCriteriaModel)
    {
        if (Auth::check()) {
            $evaluationCriteriaModel->created_by = Auth::id();
        }

        if (empty($evaluationCriteriaModel->criteria_code)) {
            $currentYear = date('Y');

            $lastEvaluationCriteriaModel = EvaluationCriteriaModel::withTrashed()
                ->whereNotNull('criteria_code')
                ->where('criteria_code', 'like', "CRIT-$currentYear-%")
                ->orderByRaw("CAST(SPLIT_PART(criteria_code, '-', 3) AS INTEGER) DESC")
                ->first();

            $nextNumber = 1;
            if ($lastEvaluationCriteriaModel) {
                preg_match('/CRIT-' . $currentYear . '-(\d+)/', $lastEvaluationCriteriaModel->criteria_code, $matches);
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }

            $evaluationCriteriaModel->criteria_code = 'CRIT-' . $currentYear . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
    }
}
