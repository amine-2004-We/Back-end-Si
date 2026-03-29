<?php

namespace App\Observers;

use App\Models\EvaluationGridOperation;
use Illuminate\Support\Facades\Auth;

class EvaluationGridOperationObserver
{
    /**
     * Handle the EvaluationGridOperation "creating" event.
     * This method is called BEFORE a model is saved for the first time.
     */
    public function creating(EvaluationGridOperation $gridOperation): void
    {
        if (Auth::check() && empty($gridOperation->user_id)) {
            $gridOperation->user_id = Auth::id();
        }
        if (empty($gridOperation->grid_code)) {
            $domainCode = '';
            if ($gridOperation->educational_area) {
                $domainCode = substr(strtoupper(preg_replace('/[^a-zA-Z]/', '', $gridOperation->educational_area)), 0, 3);
            }
            $lastGrid = EvaluationGridOperation::withTrashed()
                ->where('educational_area', $gridOperation->educational_area)
                ->whereNotNull('grid_code')
                ->orderByRaw('LENGTH(grid_code) DESC, grid_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastGrid && preg_match('/-(\d+)$/', $lastGrid->grid_code, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }

            // Format: GRID-[DOMAINE]-[XXX]
            $gridOperation->grid_code = 'GRID-' . $domainCode . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        if (empty($gridOperation->grid_version)) {
            $gridOperation->grid_version = 1;
        }
    }
    public function updating(EvaluationGridOperation $gridOperation): void
    {
        if (Auth::check() && empty($gridOperation->user_id)) {
            $gridOperation->user_id = Auth::id();
        }
    }
}
