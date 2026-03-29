<?php

namespace App\Observers;

use App\Models\ExternalTrainer;
use Carbon\Carbon;


class ExternalTrainerObserver
{
    /**
     * Handle the ExternalTrainer "created" event.
     */
    public function creating(ExternalTrainer $externalTrainer): void
    {
        if (empty($externalTrainer->trainer_identifier)) {
            $year = Carbon::now()->year;
            $prefix = 'EXTF-' . $year . '-';

            $latest = ExternalTrainer::where('trainer_identifier', 'like', $prefix . '%')
                                     ->orderBy('trainer_identifier', 'desc')
                                     ->first();
            
            $sequence = 1;
            if ($latest) {
                $lastSequence = (int) substr($latest->trainer_identifier, -4);
                $sequence = $lastSequence + 1;
            }

            $externalTrainer->trainer_identifier = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the ExternalTrainer "updated" event.
     */
    public function updated(ExternalTrainer $externalTrainer): void
    {
        //
    }

    /**
     * Handle the ExternalTrainer "deleted" event.
     */
    public function deleted(ExternalTrainer $externalTrainer): void
    {
        //
    }

    /**
     * Handle the ExternalTrainer "restored" event.
     */
    public function restored(ExternalTrainer $externalTrainer): void
    {
        //
    }

    /**
     * Handle the ExternalTrainer "force deleted" event.
     */
    public function forceDeleted(ExternalTrainer $externalTrainer): void
    {
        //
    }
}
