<?php

namespace App\Observers;

use App\Models\Level;
use App\Models\Cycle; // Importez le modèle Cycle
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LevelObserver
{
    /**
     * Handle the Level "creating" event.
     * This method is called BEFORE a model is saved for the first time.
     */
    public function creating(Level $level): void
    {
        if (Auth::check()) {
            $level->created_by = Auth::id();
        }

        if (empty($level->level_id)) {
            $cycleCode = '';
            if ($level->cycle_id) {
                $cycle = Cycle::find($level->cycle_id);
                if ($cycle) {
                    $cycleCode = $cycle->code;
                }
            }

            $lastLevel = Level::withTrashed()
                              ->where('cycle_id', $level->cycle_id)
                              ->whereNotNull('level_id')
                              ->orderByRaw('LENGTH(level_id) DESC, level_id DESC') 
                              ->first();

            $nextNumber = 1;
            if ($lastLevel) {
                preg_match('/-(\d+)$/', $lastLevel->level_id, $matches); 
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }
            $level->level_id = 'NIV-' . $cycleCode . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the Level "created" event.
     */
    public function created(Level $level): void
    {
        //
    }

    /**
     * Handle the Level "updated" event.
     */
    public function updated(Level $level): void
    {
        //
    }

    /**
     * Handle the Level "deleted" event.
     */
    public function deleted(Level $level): void
    {
        //
    }

    /**
     * Handle the Level "restored" event.
     */
    public function restored(Level $level): void
    {
        //
    }

    /**
     * Handle the Level "forceDeleted" event.
     */
    public function forceDeleted(Level $level): void
    {
        //
    }
}