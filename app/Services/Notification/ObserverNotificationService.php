<?php

namespace App\Services\Notification;

use App\Constants\Role;
use App\Models\Collaborator;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * class ObserverNotificationService
 */
class ObserverNotificationService
{
    /**
     * @var NotificationService
     */
    protected NotificationService $notificationService;

    /**
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param Model $entity The object created (Beneficiary, Project, etc.)
     * @param mixed $creator The user object (usually $entity->creator)
     */
    public function sendCreationNotification(Model $entity, mixed $creator, ?string $label = null): void
    {
        try {
            if (!$creator || !$creator->collaborator) {
                Log::warning('ObserverNotification: Missing creator/collaborator for ' . get_class($entity));
                return;
            }

            $recipients = $this->getRecipients($creator);

            if (empty($recipients)) {
                return;
            }

            $entityName = $this->resolveEntityName($entity);
            $modelLabel = $label ?? Str::snake(class_basename($entity));

            $title = "Nouvel " . strtolower($modelLabel) . " créé";

            $text = "{$creator->collaborator->first_name} {$creator->collaborator->last_name} a ajouté un nouveau {$modelLabel} : {$entityName}";


            $modalKey = 'view_' . Str::snake(class_basename($entity));

            $target = [
                'type' => 'open_modal',
                'name' => $modalKey,
                'id'   => $entity->id,
            ];

            $this->notificationService->save(
                $title,
                $text,
                $creator->collaborator->id,
                $target,
                $recipients
            );

            Log::info("Notification sent for {$modelLabel} #{$entity->id}");

        } catch (\Throwable $e) {
            Log::error("ObserverNotification Error: " . $e->getMessage());
        }
    }

    /**
     * Resolve the readable name of any object.
     * Checks for common name fields (first_name, title, name, etc).
     */
    private function resolveEntityName(Model $entity): string
    {
        if (!empty($entity->first_name)) {
            return trim($entity->first_name . ' ' . ($entity->last_name ?? ''));
        }

        if(!empty($entity->full_name)) {
            return $entity->full_name;
        }

        if (!empty($entity->title)) {
            return $entity->title;
        }

        if (!empty($entity->name)) {
            return $entity->name;
        }

        return '#' . $entity->id;
    }

    /**
     * @param $user
     * @return array
     */
    private function getRecipients($user): array
    {
        $supervisorId = $user->collaborator->superior?->id ?? null;

        $collaborators = Collaborator::where(function ($q) use ($supervisorId) {
            $q->whereHas('position', fn($q2) => $q2->where('title', Role::ADMIN_SI));

            if ($supervisorId) {
                $q->orWhere('id', $supervisorId);
            }
        })->get();

        return $collaborators->pluck('id')->unique()->values()->toArray();
    }
}
