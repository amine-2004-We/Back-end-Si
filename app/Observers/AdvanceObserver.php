<?php

namespace App\Observers;

use App\Models\Advance;
use App\Models\Collaborator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AdvanceObserver
{
    public function creating(Advance $advance): void
    {
        if (Auth::check()) {
            $advance->created_by = Auth::id();
        }

        if (empty($advance->advance_code)) {
            $year = Carbon::now()->format('Y');
            $collaborator = Collaborator::find($advance->collaborator_id);

            if (!$collaborator || empty($collaborator->collaborator_code)) {
                throw new \Exception("Collaborator or collaborator_code is required to generate advance_code.");
            }

            $collaboratorCode = $collaborator->collaborator_code;

            $lastAdvance = Advance::withTrashed()
                ->where('collaborator_id', $advance->collaborator_id)
                ->where('advance_code', 'like', "AV-{$year}-{$collaboratorCode}-%")
                ->orderByRaw('LENGTH(advance_code) DESC, advance_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastAdvance) {
                preg_match("/AV-{$year}-{$collaboratorCode}-(\d+)/", $lastAdvance->advance_code, $matches);
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }

            $advance->advance_code = "AV-{$year}-{$collaboratorCode}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
    }
}
