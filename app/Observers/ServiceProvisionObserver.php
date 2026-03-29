<?php

namespace App\Observers;

use App\Models\ServiceProvision;
use App\Models\BudgetLine;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ServiceProvisionObserver
{
    public function creating(ServiceProvision $serviceProvision): void
    {
        if (Auth::check() && is_null($serviceProvision->created_by)) {
            $serviceProvision->created_by = Auth::id();
        }

        if (is_null($serviceProvision->reference)) {
            $serviceProvision->reference = $this->generateNextReference($serviceProvision->budget_line_id);
        }
    }

    private function generateNextReference(int $budgetLineId): string
    {
        $year = Carbon::now()->year;
        // Eager load the project to ensure the relationship is available
        $budgetLine = BudgetLine::with('project')->findOrFail($budgetLineId);
        // Fallback to 'PRJ' if project or its code is not set
        $projectCode = $budgetLine->project?->code ?? 'PRJ'; 

        $prefix = "DEP-{$year}-{$projectCode}-";

        // Find the latest reference for this specific prefix
        $latestProvision = ServiceProvision::withTrashed()
            ->where('reference', 'LIKE', $prefix . '%')
            ->orderBy('reference', 'desc') // Order by the reference string itself
            ->first();

        $nextNumber = 1;
        if ($latestProvision) {
            // Extract the number from the latest reference and increment it
            $lastNumber = (int) substr($latestProvision->reference, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        $sequence = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }
}