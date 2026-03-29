<?php

namespace App\Observers;

use App\Models\Grant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GrantObserver
{
    /**
     * Handle the Grant "creating" event.
     */
    public function creating(Grant $grant): void
    {
        if (empty($grant->grant_id)) {
            $this->generateGrantId($grant);
        }
    }

    /**
     * Handle the Grant "updating" event.
     * Prevents manually setting a duplicate ID.
     */
    public function updating(Grant $grant): void
    {
        if ($grant->isDirty('grant_id') && Grant::where('grant_id', $grant->grant_id)->exists()) {
            $this->generateGrantId($grant);
        }
    }

    /**
     * Handle the Grant "restoring" event.
     * Prevents restoring a model with an ID that now exists.
     */
    public function restoring(Grant $grant): void
    {
        $existingGrant = Grant::where('grant_id', $grant->grant_id)->exists();
        if ($existingGrant) {
            $this->generateGrantId($grant);
        }
    }

    /**
     * Logic to generate the unique grant ID in the format SUBV-[AAAA]-[000X].
     * Uses a transaction and row locking to prevent race conditions.
     */
    private function generateGrantId(Grant $grant): void
    {
        DB::transaction(function () use ($grant) {
            
            $currentYear = Carbon::now()->format('Y');
            $baseIdPattern = 'SUBV-' . $currentYear . '-%';

            // 3. Find the last grant for this year, locking the relevant rows
            $lastGrant = Grant::withTrashed()
                ->where('grant_id', 'like', $baseIdPattern)
                ->orderByDesc('grant_id')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($lastGrant) {
                $lastIdParts = explode('-', $lastGrant->grant_id);
                $lastSequence = (int) end($lastIdParts); 
                $nextSequence = $lastSequence + 1;
            }

            $formattedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $grant->grant_id = 'SUBV-' . $currentYear . '-' . $formattedSequence;
        });
    }
}