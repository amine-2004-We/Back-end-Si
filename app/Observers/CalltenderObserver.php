<?php

namespace App\Observers;

use App\Models\Calltender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalltenderObserver
{
    /**
     * Handle the Calltender "creating" event.
     */
    public function creating(Calltender $calltender): void
    {
        $this->regenerateCalltenderId($calltender);
    }

    /**
     * Handle the Calltender "updating" event.
     */
    public function updating(Calltender $calltender): void
    {
        if ($calltender->isDirty('created_at') || ($calltender->isDirty('calltender_id') && Calltender::where('calltender_id', $calltender->calltender_id)->exists())) {
            $this->regenerateCalltenderId($calltender);
        }
    }

    /**
     * Handle the Calltender "restored" event.
     */
    public function restoring(Calltender $calltender): void
    {
        if (Calltender::where('calltender_id', $calltender->calltender_id)->exists()) {
            $this->regenerateCalltenderId($calltender);
        }
    }

    /**
     * Generates the next unique calltender_id in the MRC-[Year]-[Number] format.
     */
    private function regenerateCalltenderId(Calltender $calltender): void
    {
        DB::transaction(function () use ($calltender) {
          
            $yearPart = $calltender->created_at ? Carbon::parse($calltender->created_at)->format('Y') : Carbon::now()->format('Y');
            $lastCalltender = Calltender::withTrashed()
                                        ->whereYear('created_at', $yearPart)
                                        ->where('calltender_id', 'like', 'MRC-' . $yearPart . '-%')
                                        ->orderByDesc('calltender_id') 
                                        ->lockForUpdate()  
                                        ->first();

            $nextNumber = 1; 

            if ($lastCalltender) {
                $lastSuffix = (int) substr(strrchr($lastCalltender->calltender_id, '-'), 1);
                $nextNumber = $lastSuffix + 1;
            }

            $formattedNextNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $calltender->calltender_id = 'MRC-' . $yearPart . '-' . $formattedNextNumber;
        });
    }

   
  
}
