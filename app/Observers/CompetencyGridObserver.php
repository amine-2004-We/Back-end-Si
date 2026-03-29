<?php

namespace App\Observers;

use App\Models\CompetencyGrid;

class CompetencyGridObserver
{
    /**
     * Handle the CompetencyGrid "creating" event.
     */
    public function creating(CompetencyGrid $competencyGrid): void
    {
        if (blank($competencyGrid->code)) {
            $year = now()->year;
            $seq = str_pad(
                CompetencyGrid::withTrashed()->whereYear('created_at', $year)->count() + 1,
                3,
                '0',
                STR_PAD_LEFT    
            );

            $competencyGrid->code = "GRL-{$year}-{$seq}";
        }

        if (blank($competencyGrid->created_by_id) && auth()->check()) {
            $competencyGrid->created_by_id = auth()->id();
        }
    }

    /**
     * Handle the CompetencyGrid "created" event.
     */
    public function created(CompetencyGrid $competencyGrid): void
    {
        //
    }

    /**
     * Handle the CompetencyGrid "updated" event.
     */
    public function updated(CompetencyGrid $competencyGrid): void
    {
        //
    }

    /**
     * Handle the CompetencyGrid "deleted" event.
     */
    public function deleted(CompetencyGrid $competencyGrid): void
    {
        //
    }

    /**
     * Handle the CompetencyGrid "restored" event.
     */
    public function restored(CompetencyGrid $competencyGrid): void
    {
        //
    }

    /**
     * Handle the CompetencyGrid "force deleted" event.
     */
    public function forceDeleted(CompetencyGrid $competencyGrid): void
    {
        //
    }
}
