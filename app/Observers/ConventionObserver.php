<?php

namespace App\Observers;

use App\Models\Convention;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConventionObserver
{
    /**
     * Handle the Convention "created" event.
     */
    public function creating(Convention $convention): void
    {
        if (auth()->check()) {
            $convention->created_by = auth()->id();
        }
        // Génération automatique du code de convention
        if (empty($convention->agreement_code)) {
            $year = now()->year;

            // Récupérer le dernier code de l'année, même supprimé (soft delete)
            $lastConvention = Convention::withTrashed()
                ->where('agreement_code', 'like', "CONV-{$year}-%")
                ->orderBy('id', 'desc')
                ->first();

            $seq = 1;
            if ($lastConvention) {
                $parts = explode('-', $lastConvention->agreement_code);
                $lastSeq = intval(end($parts));
                $seq = $lastSeq + 1;
            }

            $convention->agreement_code = sprintf("CONV-%s-%04d", $year, $seq);
        }
        $convention->reporting_next_date = $convention->computeReportingNextDate();

        // Calcul automatique de la date de fin estimée
        if ($convention->signed_at && $convention->duration_months) {
            $months = (int) $convention->duration_months; // cast string -> int
            $signedAt = $convention->signed_at instanceof Carbon
                ? $convention->signed_at
                : Carbon::parse($convention->signed_at);

            $convention->estimated_end_date = $signedAt->copy()->addMonths($months);
        }
    }

    /**
     * Handle the Convention "updated" event.
     */
    public function updating(Convention $convention): void
    {

        if (
            $convention->isDirty('signed_at') ||
            $convention->isDirty('reporting_periodicity')
        ) {
            $convention->reporting_next_date = $convention->computeReportingNextDate();
        }
        if ($convention->signed_at && $convention->duration_months) {
            $convention->estimated_end_date = $convention->signed_at->addMonths($convention->duration_months);
        }
    }

    public function created(Convention $convention): void
    {

    }

    public function updated(Convention $convention): void
    {

    }
    
    /**
     * Handle the Convention "deleted" event.
     */
    public function deleted(Convention $convention): void
    {
        //
    }

    /**
     * Handle the Convention "restored" event.
     */
    public function restored(Convention $convention): void
    {
        //
    }

    /**
     * Handle the Convention "force deleted" event.
     */
    public function forceDeleted(Convention $convention): void
    {
        //
    }
}
