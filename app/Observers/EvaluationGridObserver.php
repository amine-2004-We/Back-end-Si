<?php

namespace App\Observers;

use App\Models\EvaluationGridModel;
use Illuminate\Support\Facades\Auth;

class EvaluationGridObserver
{
    public function creating(EvaluationGridModel $evaluationGridModel)
    {
        if (Auth::check()) {
            $evaluationGridModel->created_by = Auth::id();
        }

        if (empty($evaluationGridModel->grid_code)) {
            $currentYear = date('Y');

            $lastEvaluationGridModel = EvaluationGridModel::withTrashed()
                ->whereNotNull('grid_code')
                ->where('grid_code', 'like', "GRID-$currentYear-%")
                ->orderByRaw("CAST(split_part(grid_code, '-', 3) AS INTEGER) DESC")
                ->first();

            $nextNumber = 1;
            if ($lastEvaluationGridModel) {
                preg_match('/GRID-' . $currentYear . '-(\d+)/', $lastEvaluationGridModel->grid_code, $matches);
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }

            $evaluationGridModel->grid_code = 'GRID-' . $currentYear . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
    }
}
