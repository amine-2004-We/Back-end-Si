<?php

namespace App\Observers;

use App\Models\EvaluationOperation;
use Illuminate\Support\Facades\Auth;

class EvaluationOperationObserver
{
    /**
     * Handle the EvaluationOperation "creating" event.
     * This method is called BEFORE a model is saved for the first time.
     */
    public function creating(EvaluationOperation $evaluation): void
    {
        if (Auth::check()) {
            $evaluation->created_by = Auth::id();
        }

        if (empty($evaluation->evaluation_code)) {
            $beneficiaryCode = $evaluation->beneficiary?->beneficiary_id ?? 'UNKNOWN';
            $lastEval = EvaluationOperation::withTrashed()
                ->where('beneficiary_id', $evaluation->beneficiary_id)
                ->orderByRaw('LENGTH(evaluation_code) DESC, evaluation_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastEval) {
                if (preg_match('/EVAL-(.+)-(\d+)$/', $lastEval->evaluation_code, $matches)) {
                    $nextNumber = (int) $matches[2] + 1;
                }
            }
            $evaluation->evaluation_code = sprintf(
                'EVAL-%s-%03d',
                strtoupper($beneficiaryCode),
                $nextNumber
            );
        }
    }
}
