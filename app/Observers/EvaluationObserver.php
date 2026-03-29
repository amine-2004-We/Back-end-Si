<?php

namespace App\Observers;

use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

class EvaluationObserver
{
    public function creating(Evaluation $evaluation)
    {
        if (Auth::check()) {
            $evaluation->created_by = Auth::id();
        }

        if (empty($evaluation->evaluation_code)) {
            $nextNumber = 1;

            if (!empty($evaluation->object_project)) {
                $lastEvaluationModel = Evaluation::withTrashed()
                    ->whereNotNull('evaluation_code')
                    ->where('evaluation_code', 'like', 'EVAL-PROJ-%')
                    ->orderByRaw("CAST(split_part(evaluation_code, '-', 3) AS INTEGER) DESC")
                    ->first();

                if ($lastEvaluationModel) {
                    if (preg_match('/EVAL-PROJ-(\d+)/', $lastEvaluationModel->evaluation_code, $matches)) {
                        $nextNumber = (int)$matches[1] + 1;
                    }
                }

                $evaluation->evaluation_code = 'EVAL-PROJ-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            } elseif (!empty($evaluation->object_partner)) {
                $lastEvaluationModel = Evaluation::withTrashed()
                    ->whereNotNull('evaluation_code')
                    ->where('evaluation_code', 'like', 'EVAL-PART-%')
                    ->orderByRaw("CAST(split_part(evaluation_code, '-', 3) AS INTEGER) DESC")
                    ->first();

                if ($lastEvaluationModel) {
                    if (preg_match('/EVAL-PART-(\d+)/', $lastEvaluationModel->evaluation_code, $matches)) {
                        $nextNumber = (int)$matches[1] + 1;
                    }
                }

                $evaluation->evaluation_code = 'EVAL-PART-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        }
    }
}
