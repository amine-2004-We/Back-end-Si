<?php

namespace App\Observers;

use App\Models\Cycle;
use Illuminate\Support\Facades\Auth;

class CycleObserver
{
    /**
     * Handle the Cycle "creating" event.
     * This method is called BEFORE a model is saved for the first time.
     */
    public function creating(Cycle $cycle): void
    {
        if (Auth::check()) {
            $cycle->created_by = Auth::id();
        }

        if (empty($cycle->cycle_id)) {
            $lastCycle = Cycle::withTrashed()
                               ->whereNotNull('cycle_id')
                               ->orderByRaw('LENGTH(cycle_id) DESC, cycle_id DESC') 
                               ->first();

            $nextNumber = 1;
            if ($lastCycle) {
                preg_match('/CYC-(\d+)/', $lastCycle->cycle_id, $matches);
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }
            $cycle->cycle_id = 'CYC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }


    }

    /**
     * Handle the Cycle "created" event.
     */
    public function created(Cycle $cycle): void
    {
        //
    }

    /**
     * Handle the Cycle "updated" event.
     */
    public function updated(Cycle $cycle): void
    {
        //
    }

    /**
     * Handle the Cycle "deleted" event.
     */
    public function deleted(Cycle $cycle): void
    {
        //
    }

    /**
     * Handle the Cycle "restored" event.
     */
    public function restored(Cycle $cycle): void
    {
        //
    }

    /**
     * Handle the Cycle "forceDeleted" event.
     */
    public function forceDeleted(Cycle $cycle): void
    {
        //
    }
}