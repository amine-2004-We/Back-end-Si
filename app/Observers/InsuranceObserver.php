<?php

namespace App\Observers;

use App\Models\Insurance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InsuranceObserver
{
    /**
     * Handle the Insurance "creating" event.
     */
    public function creating(Insurance $insurance): void
    {
        if (empty($insurance->insurance_id)) {
            $this->generateInsuranceId($insurance);
        }
    }

    /**
     * Handle the Insurance "updating" event.
     * Prevents manually setting a duplicate ID.
     */
    public function updating(Insurance $insurance): void
    {
        if ($insurance->isDirty('insurance_id') && Insurance::where('insurance_id', $insurance->insurance_id)->exists()) {
            $this->generateInsuranceId($insurance);
        }
    }

    /**
     * Handle the Insurance "restoring" event.
     * Prevents restoring a model with an ID that now exists.
     */
    public function restoring(Insurance $insurance): void
    {
        $existingInsurance = Insurance::where('insurance_id', $insurance->insurance_id)->exists();
        if ($existingInsurance) {
            $this->generateInsuranceId($insurance);
        }
    }

    /**
     * Logic to generate the unique insurance ID in the format ASSUR-[ID collab]-[Type]-[Année].
     * Uses a transaction and row locking to prevent race conditions.
     */
    private function generateInsuranceId(Insurance $insurance): void
    {
        DB::transaction(function () use ($insurance) {
            
            // 1. Get the collaborator ID (assuming relationship exists)
            $collaboratorId = $insurance->collaborator_id;
            
            // 2. Get the insurance type (ensure it's trimmed and uppercase)
            $insuranceType = Str::upper(trim($insurance->insurance_type));
            
            // 3. Get the current year
            $currentYear = Carbon::now()->format('Y');

            // 4. Build the base pattern to search for the last ID for THIS collaborator, type, and year
            $baseIdPattern = 'ASSUR-' . $collaboratorId . '-' . $insuranceType . '-' . $currentYear . '%';

            // 5. Find the last insurance for this collaborator/type/year, locking the relevant rows
            $lastInsurance = Insurance::withTrashed()
                ->where('insurance_id', 'like', $baseIdPattern)
                ->orderByDesc('insurance_id')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            // 6. If a previous insurance exists, extract its sequence number and increment
            if ($lastInsurance) {
                // Extract the sequence number from the end of the insurance_id
                // Format: ASSUR-123-MEDICAL-2024-001
                $lastIdParts = explode('-', $lastInsurance->insurance_id);
                $lastSequence = (int) end($lastIdParts);
                $nextSequence = $lastSequence + 1;
            }

            // 7. Format the sequence number with a padding of 3 zeros
            $formattedSequence = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            // 8. Combine all parts to create the final, unique insurance_id
            $insurance->insurance_id = 'ASSUR-' . $collaboratorId . '-' . $insuranceType . '-' . $currentYear . '-' . $formattedSequence;
        });
    }
}