<?php

namespace App\Observers;

use App\Models\ExpenseNote;
use Carbon\Carbon;

class ExpenseNoteObserver
{
    /**
     * @param ExpenseNote $note
     */
    public function creating(ExpenseNote $note): void
    {
        if (!empty($note->code)) {
            return;
        }

        $year = $note->expense_date
            ? Carbon::parse($note->expense_date)->year
            : now()->year;

        $prefix = "NF-{$year}-";

        $lastCode = ExpenseNote::withTrashed()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        $next = 1;
        if ($lastCode) {
            $num = (int) substr($lastCode, -4);
            $next = $num + 1;
        }

        $note->code = sprintf('%s%04d', $prefix, $next);
    }
}
