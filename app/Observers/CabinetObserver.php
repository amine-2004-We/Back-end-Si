<?php

namespace App\Observers;

use App\Models\Cabinet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CabinetObserver
{
    /**
     * @param  \App\Models\Cabinet  $cabinet
     * @return void
     */
    public function creating(Cabinet $cabinet): void
    {
        if (empty($cabinet->created_by_id) && Auth::check()) {
            $cabinet->created_by_id = Auth::id();
        }

        if (empty($cabinet->cabinet_id)) {
            $currentYear = Carbon::now()->year;

            $sequence = Cabinet::whereYear('created_at', $currentYear)->withTrashed()->count() + 1;

            $paddedSequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $cabinet->cabinet_id = 'CAB-' . $currentYear . '-' . $paddedSequence;
        }
    }
}
