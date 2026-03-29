<?php

namespace App\Observers;

use App\Models\Position;

class PositionObserver
{
    public function creating(Position $position)
    {
        $nextNumber = Position::withTrashed()->count() + 1;
        $position->position_code = 'FONC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    }
}
