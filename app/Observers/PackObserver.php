<?php

namespace App\Observers;

use App\Models\Pack;
use Carbon\Carbon;

class PackObserver
{
    public function creating(Pack $pack): void
    {
        $pack->pack_id = $this->generateNextPackId();
        // Pas besoin d'assigner created_at, Laravel le gère automatiquement
    }

    private function generateNextPackId(): string
    {
        $year = Carbon::now()->year;

        $last = Pack::withTrashed()
            ->where('pack_id', 'like', "PACK-{$year}-%")
            ->orderByDesc('pack_id')
            ->first();

        $nextNumber = 1;

        if ($last) {
            $lastSuffix = (int) substr($last->pack_id, strlen("PACK-{$year}-"));
            $nextNumber = $lastSuffix + 1;
        }

        // Formater avec 3 chiffres et zéros à gauche, ex: 001
        $suffix = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "PACK-{$year}-{$suffix}";
    }
}
