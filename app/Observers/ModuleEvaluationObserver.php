<?php

namespace App\Observers;

use App\Models\ModuleEvaluation;
use Illuminate\Support\Facades\Auth;

class ModuleEvaluationObserver
{
    public function creating(ModuleEvaluation $model): void
    {
        // Assign creator if not provided
        if (empty($model->created_by_id) && Auth::check()) {
            $model->created_by_id = Auth::id();
        }

        // Generate a unique human code if missing
        if (empty($model->evaluation_id)) {
            $base = ModuleEvaluation::generateEvaluationIdBase(); // e.g. "MEV-20240903"
            // First attempt: use daily sequence: MEV-YYYYMMDD-####.
            $seq = (int) ModuleEvaluation::withTrashed()
                ->where('evaluation_id', 'like', $base . '%')
                ->count() + 1;

            $code = sprintf('%s-%04d', $base, $seq);

            // Ensure uniqueness (handles race conditions)
            $attempts = 0;
            while (
                ModuleEvaluation::withTrashed()
                    ->where('evaluation_id', $code)
                    ->exists()
                && $attempts < 10
            ) {
                $seq++;
                $code = sprintf('%s-%04d', $base, $seq);
                $attempts++;
            }

            // Final fallback if still colliding
            if (
                ModuleEvaluation::withTrashed()
                    ->where('evaluation_id', $code)
                    ->exists()
            ) {
                $code = $base . '-' . now()->format('His') . '-' . random_int(100, 999);
            }

            $model->evaluation_id = $code;
        }
    }
}
