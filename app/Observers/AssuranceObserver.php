<?php

namespace App\Observers;

use App\Models\Assurance;
use Carbon\Carbon;

class AssuranceObserver
{
    /**
     * Handle the Assurance "creating" event.
     */
    public function creating(Assurance $assurance): void
    {
        if (empty($assurance->insurance_id)) {
            $year = Carbon::now()->year;
            $latestAssurance = Assurance::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextId = $latestAssurance ? (int)substr($latestAssurance->insurance_id, -4) + 1 : 1;

            $assurance->insurance_id = sprintf('ASSUR-%d-%04d', $year, $nextId);
        }
    }
}
