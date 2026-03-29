<?php

namespace App\Observers;

use App\Models\Training;
use Illuminate\Support\Facades\Auth;

class TrainingObserver
{
    /**
     * @param  Training  $training
     * @return void
     */
    public function creating(Training $training): void
    {
        if (empty($training->created_by_id) && Auth::check()) {
            $training->created_by_id = Auth::id();
        }

        if (empty($training->training_id)) {
            $this->generateTrainingId($training);
        }
    }

    /**
     * @param  \App\Models\Training  $training
     * @return void
     */
    public function updating(Training $training): void
    {
        if ($training->isDirty('training_type')) {
            $this->generateTrainingId($training);
        }
    }

    /**
     * @param  \App\Models\Training  $training
     * @return void
     */
    protected function generateTrainingId(Training $training): void
    {
        $typeAbbreviations = [
            'initial'    => 'INIT',
            'continuous' => 'CONT',
            'monthly'    => 'MONT',
        ];

        $type = $training->training_type;
        $abbreviation = $typeAbbreviations[$type] ?? 'UNKNOWN';

        // Include soft-deleted records and get the max sequence number
        $prefix = 'FORM-' . $abbreviation . '-';
        
        $lastTraining = Training::withTrashed()
            ->where('training_type', $type)
            ->where('training_id', 'LIKE', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(training_id FROM '\d+$') AS INTEGER) DESC")
            ->first();

        if ($lastTraining && preg_match('/(\d+)$/', $lastTraining->training_id, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        $paddedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $training->training_id = $prefix . $paddedSequence;
    }
}
