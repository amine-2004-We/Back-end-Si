<?php

namespace App\Observers;
use App\Models\EvaluationCriteriaOperationModal;
use Illuminate\Support\Facades\Auth;

class EvaluationCriteriaOperationsObserver
{
    public function creating(EvaluationCriteriaOperationModal $criteria)
    {
        if (Auth::check()) {
            $criteria->created_by = Auth::id();
        }

        if (empty($criteria->criteria_id) && $criteria->gridEvaluation) {
            $gridCode = $criteria->gridEvaluation->grid_code;

            $count = EvaluationCriteriaOperationModal::withTrashed()
                ->where('grid_evaluation_id', $criteria->grid_evaluation_id)
                ->count();

            $criteriaNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
            $criteria->criteria_id = 'CRIT-' . $gridCode . '-' . $criteriaNumber;
        }
    }

    public function updating(EvaluationCriteriaOperationModal $criteria)
    {
        if (Auth::check()) {
            $criteria->created_by = Auth::id();
        }
    }
}
