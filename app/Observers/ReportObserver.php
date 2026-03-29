<?php

namespace App\Observers;

use App\Models\Report;

class ReportObserver
{
    /**
     * Handle the Report "creating" event.
     *
     * @param  \App\Models\Report  $report
     * @return void
     */
    public function creating(Report $report)
    {
        // Define a mapping for report types to their short codes based on documentation
        // Example: 'Visite d\'accompagnement' -> 'VIS'
        $typeMap = [
            'Réunion' => 'REU',
            'Visite' => 'VIS',
            'Événement' => 'EVE',
            'Évaluation' => 'EVA',
            'Autre' => 'AUT',
            // Add other types as needed
        ];

        // Get the short code for the current report type. If the type is not found,
        // we'll default to a generic code, though it's best to have all types mapped.
        $objectCode = $typeMap[$report->type] ?? 'GEN';

        // Find the last report ID for the same object type to get the next sequential number.
        // We use a `like` query to find IDs that match the pattern for this specific type.
        $lastReport = Report::where('report_id', 'like', "CR-{$objectCode}-%")
                             ->orderBy('report_id', 'desc')
                             ->first();

        // Initialize the last number to 0.
        $lastNumber = 0;
        if ($lastReport) {
            // Extract the numeric part of the last ID string.
            // Example: 'CR-VIS-002' -> '002' -> 2
            $lastNumber = (int) substr($lastReport->report_id, -3);
        }

        // Increment the number and format it with leading zeros to be exactly 3 digits.
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        // Finally, set the `report_id` on the model before it's saved.
        $report->report_id = "CR-{$objectCode}-{$newNumber}";
    }
}
