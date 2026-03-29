<?php

namespace App\Observers;

use App\Models\Checkout;
use Illuminate\Support\Facades\Auth;

class CheckoutObserver
{
    public function creating(Checkout $checkout)
    {
        $year = now()->year;
        $nextNumber = Checkout::withTrashed()
                ->whereYear('created_at', $year)
                ->count()+1;
        $checkout->code='CAISSE-'.$year.'-'.$nextNumber;
        $checkout->available_balance=$checkout->initiale_amount-$checkout->used_amount;
        if(Auth::check())
        {
            $checkout->created_by=Auth::id();
        }
    }
    public function updating(Checkout $checkout)
    {
        if ($checkout->isDirty(['initiale_amount', 'used_amount'])) {
            $checkout->available_balance = $checkout->initiale_amount - $checkout->used_amount;
        }
    }

}
