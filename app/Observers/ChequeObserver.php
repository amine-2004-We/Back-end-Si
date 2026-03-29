<?php

namespace App\Observers;

use App\Models\Cheque;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChequeObserver
{
    public function creating(Cheque $cheque): void
    {
        if (Auth::check() && is_null($cheque->created_by)) {
            $cheque->created_by = Auth::id();
        }

        if (is_null($cheque->cheque_id)) {
            $cheque->cheque_id = $this->generateNextChequeId();
        }
    }

    private function generateNextChequeId(): string
    {
        $year = Carbon::now()->year;
        $latestCheque = Cheque::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestCheque) {
            preg_match('/\-(\d+)$/', $latestCheque->cheque_id, $matches);
            if (isset($matches[1])) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
        }

        return "CHQ-{$year}-{$nextNumber}";
    }
}