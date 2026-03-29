<?php

namespace App\Observers;

use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CandidateObserver
{
    /**
     * Handle the Candidate "creating" event.
     */
    public function creating(Candidate $candidate): void
    {
        if (empty($candidate->candidate_id)) {
            $this->generateCandidateId($candidate);
        }
    }

    /**
     * Handle the Candidate "updating" event.
     */
    public function updating(Candidate $candidate): void
    {

        if ($candidate->isDirty('candidate_id') && Candidate::where('candidate_id', $candidate->candidate_id)->exists()) {
            $this->generateCandidateId($candidate);
        }
    }

    /**
     * Handle the Candidate "restoring" event.
     */
    public function restoring(Candidate $candidate): void
    {
        $existingCandidate = Candidate::where('candidate_id', $candidate->candidate_id)->exists();
        if ($existingCandidate) {
            $this->generateCandidateId($candidate);
        }
    }

    /**
     * Logic to generate the unique candidate ID in the format CND-[Nom]-[Date réception].
     */
    private function generateCandidateId(Candidate $candidate): void
    {
        DB::transaction(function () use ($candidate) {
            $nom = $this->formatName($candidate->last_name);
 
            $dateReception = $candidate->created_at 
                ? $candidate->created_at->format('dmY')
                : now()->format('dmY');

            $baseId = 'CND-' . $nom . '-' . $dateReception;
  
            $existingCount = Candidate::withTrashed()
                ->where('candidate_id', 'like', $baseId . '%')
                ->count();

            if ($existingCount > 0) {
                $candidate->candidate_id = $baseId . '-' . ($existingCount + 1);
            } else {
                $candidate->candidate_id = $baseId;
            }
        });
    }

    /**
     * Format the name for use in the ID.
     */
    private function formatName(string $name): string
    {
        // Remove accents and special characters
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        
        // Convert to uppercase
        $name = strtoupper($name);
        
        // Remove non-alphanumeric characters (except hyphens)
        $name = preg_replace('/[^A-Z0-9-]/', '', $name);
        
        // Limit to 15 characters for brevity
        return substr($name, 0, 15);
    }
}