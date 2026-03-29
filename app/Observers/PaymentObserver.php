<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentObserver
{
    public function creating(Payment $payment)
    {
        $year = now()->year;
        $nextNumber = Payment::withTrashed()
                ->whereYear('created_at', $year)
                ->count() + 1;
        $payment->payment_number = 'PAY-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        if (Auth::check()) {
            $payment->created_by = Auth::id();
        }
    }
}
