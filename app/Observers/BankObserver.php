<?php

namespace App\Observers;

use App\Models\Bank;

/**
 * class BankObserver
 */
class BankObserver
{
    /**
     * @param Bank $bank
     * @return void
     */
    public function creating(Bank $bank): void
    {
        $year = $bank->created_at?->format('Y') ?? now()->format('Y');

        $count = Bank::withTrashed()
                ->whereYear('created_at', $year)
                ->count() + 1;

        $bank->bank_id = $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function updating(Bank $bank): void
    {
        $bank->bank_id = $bank->bank_id ?? $bank->created_at?->format('Y') . str_pad($bank->id, 4, '0', STR_PAD_LEFT);
    }
}
