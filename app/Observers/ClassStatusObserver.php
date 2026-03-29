<?php

namespace App\Observers;

use App\Models\ClassStatusHistory;
use App\Models\ProjectClass;

class ClassStatusObserver
{
    /**
     * Handle the ProjectClass "updated" event.
     */
    public function updated(ProjectClass $class): void
    {
        $changes = $class->getChanges();

        // Track state changes
        if ($class->isDirty('class_state')) {
            $oldState = $class->getOriginal('class_state');
            $newState = $class->getAttribute('class_state');

            ClassStatusHistory::create([
                'class_id' => $class->id,
                'change_type' => 'state',
                'old_value' => $oldState?->value,
                'new_value' => $newState?->value,
                'change_date' => now()->toDateString(),
                'transfer_to_project_id' => $newState?->value === 'Transfert' ? $class->transfer_to_project_id : null,
                'relocate_to_class_id' => $newState?->value === 'Relocalisation' ? $class->relocate_to_class_id : null,
                'user_id' => auth()->id() ?? $class->user_id,
            ]);
        }

        // Track status changes
        if ($class->isDirty('class_status_value')) {
            $oldStatus = $class->getOriginal('class_status_value');
            $newStatus = $class->getAttribute('class_status_value');

            ClassStatusHistory::create([
                'class_id' => $class->id,
                'change_type' => 'status',
                'old_value' => $oldStatus?->value,
                'new_value' => $newStatus?->value,
                'change_date' => $class->status_change_date ?? now()->toDateString(),
                'reason' => $class->status_change_reason,
                'perpetuation_project_id' => $newStatus?->value === 'Pérennisé' ? $class->perpetuation_project_id : null,
                'user_id' => auth()->id() ?? $class->user_id,
            ]);
        }
    }

    /**
     * Handle the ProjectClass "created" event.
     */
    public function created(ProjectClass $class): void
    {
        // Log the initial state creation
        ClassStatusHistory::create([
            'class_id' => $class->id,
            'change_type' => 'state',
            'old_value' => null,
            'new_value' => $class->class_state?->value ?? 'Création',
            'change_date' => now()->toDateString(),
            'user_id' => auth()->id() ?? $class->user_id,
        ]);

        // Log the initial status creation
        ClassStatusHistory::create([
            'class_id' => $class->id,
            'change_type' => 'status',
            'old_value' => null,
            'new_value' => $class->class_status_value?->value ?? 'Opérationnel',
            'change_date' => now()->toDateString(),
            'user_id' => auth()->id() ?? $class->user_id,
        ]);
    }
}
