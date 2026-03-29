<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\NotificationService;

/**
 * class TaskObserver
 */
class TaskObserver
{
    /**
     * Handle the Task "creating" event.
     */
    public function creating(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if (!$task->isProject) {
            return;
        }

        try {
            $responsibleCollaborator = $task->responsibleCollaborator;

            if (!$responsibleCollaborator) {
                \Log::warning('TaskObserver created skipped: responsible collaborator missing', [
                    'task_id' => $task->id,
                    'responsible_collaborator_id' => $task->responsible_collaborator_id,
                ]);
                return;
            }

            $recipientIds = [$responsibleCollaborator->id];

            $title = "Nouvelle tâche créée vous est assignée";
            $text = "La tâche **{$task->title}** vous a été assignée";

            $senderId = $task->creator?->collaborator->id ;

            $target = [
                'type' => 'open_modal',
                'name' => 'view_task',
                'id' => $task->id,
            ];

            $notification = app(NotificationService::class)->save(
                $title,
                $text,
                $senderId,
                $target,
                $recipientIds
            );

            \Log::info('TaskObserver created notification', [
                'notification_id' => $notification->id ?? null,
                'task_id' => $task->id,
                'recipient_id' => $responsibleCollaborator->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('TaskObserver created error: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
