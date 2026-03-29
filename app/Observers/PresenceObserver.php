<?php

namespace App\Observers;

use App\Models\Presence;
use App\Models\Task;

class PresenceObserver
{
    /**
     * Handle the Presence "creating" event.
     */
    public function creating(Presence $presence): void
    {
        if (empty($presence->presence_id)) {
            $task = Task::find($presence->task_id);
            $person = $presence->personable;

            $prefix = 'PRS';
            $objectPrefix = $task ? 'TASK' . $task->id : 'OBJ';
            $personPrefix = $person ? strtoupper(class_basename($person)) . $person->id : 'PERSON';

            $presence->presence_id = "{$prefix}-{$objectPrefix}-{$personPrefix}";
        }
    }
}
