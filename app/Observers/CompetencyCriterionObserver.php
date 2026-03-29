<?php

namespace App\Observers;

use App\Models\CompetencyCriterion;
use Illuminate\Support\Facades\Auth;

class CompetencyCriterionObserver
{
    /**
     * Handle the CompetencyCriterion "creating" event.
     */
    public function creating(CompetencyCriterion $competencyCriterion): void
    {
        if (is_null($competencyCriterion->identifier)) {
            $lastId = CompetencyCriterion::withTrashed()->max('id') ?? 0;
            $competencyCriterion->identifier = 'CRIT-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        }

        if (Auth::check() && is_null($competencyCriterion->created_by_id)) {
            $competencyCriterion->created_by_id = Auth::id();
        }
    }
}
