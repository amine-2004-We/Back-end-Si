<?php

namespace App\Observers;

use App\Models\Payroll;
use Illuminate\Support\Carbon;

class PayrollObserver
{
    /**
     * Handle the Payroll "creating" event.
     */
    public function creating(Payroll $payroll): void
    {
        if (!empty($payroll->payroll_code)) {
            return;
        }

        if (empty($payroll->period) || empty($payroll->collaborator_id)) {
            return;
        }

        $period = Carbon::parse($payroll->period);
        $month  = $period->format('m');
        $year   = $period->format('Y');

        $baseCode = sprintf('PAYE-%s-%s-%s', $month, $year, $payroll->collaborator_id);

        $code = $baseCode;
        $suffix = 2;

        while (
            Payroll::query()
                ->where('payroll_code', $code)
                ->exists()
        ) {
            $code = $baseCode.'-'.$suffix;
            $suffix++;
        }

        $payroll->payroll_code = $code;
    }
}
