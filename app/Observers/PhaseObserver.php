<?php

namespace App\Observers;

use App\Models\Phase;

class PhaseObserver
{
    /**
     * Handle the Phase "created" event.
     */
    public function creating(Phase $phase): void
    {
        if (empty($phase->phase_identifier) && !empty($phase->project_id)) {
            $prefix = 'PHASE-' . $phase->project_id . '-';

            $latestPhase = Phase::where('project_id', $phase->project_id)
                                ->where('phase_identifier', 'like', $prefix . '%')
                                ->orderBy('phase_identifier', 'desc')
                                ->first();

            $sequence = 1;
            if ($latestPhase) {
                $lastSequence = (int) substr($latestPhase->phase_identifier, -3);
                $sequence = $lastSequence + 1;
            }

            $phase->phase_identifier = $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the Phase "updated" event.
     */
    public function updated(Phase $phase): void
    {
        //
    }

    /**
     * Handle the Phase "deleted" event.
     */
    public function deleted(Phase $phase): void
    {
        //
    }

    /**
     * Handle the Phase "restored" event.
     */
    public function restored(Phase $phase): void
    {
        //
    }

    /**
     * Handle the Phase "force deleted" event.
     */
    public function forceDeleted(Phase $phase): void
    {
        //
    }
}
