<?php

namespace App\Observers;

use App\Models\ClassResource;
use App\Models\ClassResourceHistory;
use Illuminate\Support\Facades\Auth;

class ClassResourceObserver
{
    /**
     * Handle the ClassResource "created" event.
     */
    public function created(ClassResource $classResource): void
    {
        ClassResourceHistory::create([
            'class_resource_id' => $classResource->id,
            'class_id' => $classResource->class_id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'new_values' => $classResource->toArray(),
            'change_description' => "Resource '{$classResource->educator}' added to class",
        ]);
    }

    /**
     * Handle the ClassResource "updated" event.
     */
    public function updated(ClassResource $classResource): void
    {
        $changes = [];
        $oldValues = [];
        $newValues = [];

        // Get the original values before update
        $original = $classResource->getOriginal();
        $current = $classResource->getAttributes();

        // Compare old and new values
        foreach ($current as $key => $value) {
            if (isset($original[$key]) && $original[$key] != $value && !in_array($key, ['updated_at', 'created_at'])) {
                $oldValues[$key] = $original[$key];
                $newValues[$key] = $value;
                $changes[] = "{$key}: '{$original[$key]}' → '{$value}'";
            }
        }

        // Only create history if there are actual changes
        if (!empty($changes)) {
            ClassResourceHistory::create([
                'class_resource_id' => $classResource->id,
                'class_id' => $classResource->class_id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'change_description' => 'Changes: ' . implode(', ', $changes),
            ]);
        }
    }

    /**
     * Handle the ClassResource "deleted" event.
     */
    public function deleted(ClassResource $classResource): void
    {
        ClassResourceHistory::create([
            'class_resource_id' => $classResource->id,
            'class_id' => $classResource->class_id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'old_values' => $classResource->toArray(),
            'change_description' => "Resource '{$classResource->educator}' removed from class",
        ]);
    }

    /**
     * Handle the ClassResource "restored" event.
     */
    public function restored(ClassResource $classResource): void
    {
        ClassResourceHistory::create([
            'class_resource_id' => $classResource->id,
            'class_id' => $classResource->class_id,
            'user_id' => Auth::id(),
            'action' => 'restored',
            'new_values' => $classResource->toArray(),
            'change_description' => "Resource '{$classResource->educator}' restored",
        ]);
    }

    /**
     * Handle the ClassResource "force deleted" event.
     */
    public function forceDeleted(ClassResource $classResource): void
    {
        ClassResourceHistory::create([
            'class_resource_id' => $classResource->id,
            'class_id' => $classResource->class_id,
            'user_id' => Auth::id(),
            'action' => 'force_deleted',
            'old_values' => $classResource->toArray(),
            'change_description' => "Resource '{$classResource->educator}' permanently deleted",
        ]);
    }
}
