<?php

namespace App\Observers;

use App\Models\Training;
use App\Models\TrainingGroup;

class TrainingGroupObserver
{
    public function creating(TrainingGroup $trainingGroup)
    {
        $trainingId = Training::find($trainingGroup->training_id)->training_id;
        $count = TrainingGroup::withTrashed()->where('training_id', $trainingGroup->training_id)->count();
        $trainingGroup->group_id = 'GRP-' . $trainingId . '-' . ($count + 1);
    }

    public function updating(TrainingGroup $trainingGroup)
    {
        if($trainingGroup->isDirty('training_id')) {
            $trainingId = Training::find($trainingGroup->training_id)->training_id;
            $count = TrainingGroup::withTrashed()->where('training_id', $trainingGroup->training_id)->count();
            $trainingGroup->group_id = 'GRP-' . $trainingId . '-' . ($count + 1);
        }
    }
}
