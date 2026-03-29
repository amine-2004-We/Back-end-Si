<?php

namespace App\Observers;

use App\Models\External;
use Carbon\Carbon;

class ExternalObserver
{
    /**
     * Handle the External "creating" event.
     * Generates the unique identifier.
     */
    public function creating(External $external): void
    {
        if (empty($external->external_identifier)) {
            $year = Carbon::now()->year;
            $prefix = 'EXT-' . $year . '-';

            $latest = External::where('external_identifier', 'like', $prefix . '%')
                ->orderBy('external_identifier', 'desc')
                ->first();

            $sequence = 1;
            if ($latest) {
                $lastSequence = (int) substr($latest->external_identifier, -4);
                $sequence = $lastSequence + 1;
            }

            $external->external_identifier = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }
        
    
    }

    /**
     * Handle the External "updated" event.
     */
    public function updated(External $external): void
    {
        //
    }

    /**
     * Handle the External "deleted" event.
     */
    public function deleted(External $external): void
    {
        //
    }

    /**
     * Handle the External "restored" event.
     */
    public function restored(External $external): void
    {
        //
    }

    /**
     * Handle the External "force deleted" event.
     */
    public function forceDeleted(External $external): void
    {
        //
    }
}
