<?php

namespace App\Observers;

use App\Models\Avenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvenantObserver
{
    /**
     * Handle the Avenant "creating" event.
     */
    public function creating(Avenant $avenant): void
    {
        $this->regenerateAvenantId($avenant);
    }

    /**
     * Handle the Avenant "updating" event.
     */
    public function updating(Avenant $avenant): void
    {
        if ($avenant->isDirty('created_at') || ($avenant->isDirty('avenant_id') && Avenant::where('avenant_id', $avenant->avenant_id)->exists())) {
            $this->regenerateAvenantId($avenant);
        }
    }

    /**
     * Handle the Avenant "restored" event.
     */
    public function restoring(Avenant $avenant): void
    {
        if (Avenant::where('avenant_id', $avenant->avenant_id)->exists()) {
            $this->regenerateAvenantId($avenant);
        }
    }

    /**
     * Generates the next unique avenant_id in the AVN-[Marche ID]-[Number] format.
     */
    private function regenerateAvenantId(Avenant $avenant): void
    {
        DB::transaction(function () use ($avenant) {
            $marcheId = $avenant->marche_id;
            
            // Get the last avenant for this marche
            $lastAvenant = Avenant::withTrashed()
                                ->where('marche_id', $marcheId)
                                ->where('avenant_id', 'like', 'AVN-' . $marcheId . '-%')
                                ->orderByDesc('avenant_id') 
                                ->lockForUpdate()  
                                ->first();

            $nextNumber = 1; 

            if ($lastAvenant) {
                $lastSuffix = (int) substr(strrchr($lastAvenant->avenant_id, '-'), 1);
                $nextNumber = $lastSuffix + 1;
            }

            $formattedNextNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $avenant->avenant_id = 'AVN-' . $marcheId . '-' . $formattedNextNumber;
        });
    }
}

