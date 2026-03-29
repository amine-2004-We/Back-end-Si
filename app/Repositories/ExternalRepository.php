<?php

namespace App\Repositories;

use App\Models\External;
use App\Models\ExternalAttachment; // Import the model
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Import Storage facade

/**
 *class ExternalRepository
 */
class ExternalRepository
{
    /**
     * @var array|string[]
     */
    public array $defaultWith = ['trainings', 'creator', 'participant','attachments', 'trainingGroup'];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = External::query()->with($this->defaultWith)->withTrashed();

        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('full_name', 'like', '%' . $value . '%')
                          ->orWhere('email', 'like', '%' . $value . '%')
                          ->orWhere('external_identifier', 'like', '%' . $value . '%');
                    });
                    break;

                case 'training_id':
                    $query->whereHas('trainings', function (Builder $q) use ($value) {
                        $q->where('trainings.id', $value);
                    });
                    break;

                case 'activation_status':
                    if ($value === 'active') {
                        $query->whereNull('deleted_at');
                    } elseif ($value === 'deactivated') {
                        $query->whereNotNull('deleted_at');
                    }
                    break;
            }
        }

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
                     ->paginate($perPage);
    }

    /**
     * @param int $id
     * @return External|null
     */
    public function findById(int $id): ?External
    {
        return External::withTrashed()->with($this->defaultWith)->find($id);
    }

    /**
     * @param array $data
     * @return External
     */
    public function create(array $data): External
    {
        return External::create($data);
    }

    /**
     * @param External $external
     * @param array $data
     * @return bool
     */
    public function update(External $external, array $data): bool
    {
        return $external->update($data);
    }

    /**
     * @param External $external
     * @return bool|null
     */
    public function delete(External $external): ?bool
    {
        return $external->delete();
    }

    /**
     * @param External $external
     * @param array $trainings
     * @return void
     */
    public function syncTrainings(External $external, array $trainings): void
    {
        $syncData = [];
        foreach ($trainings as $training) {
            $syncData[$training['id']] = [
                'training_evaluation' => $training['training_evaluation'] ?? null,
                'satisfaction_evaluation' => $training['satisfaction_evaluation'] ?? null,
            ];
        }
        $external->trainings()->sync($syncData);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $external = External::withTrashed()->find($id);
            if (!$external) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Externe non trouvé.'];
                continue;
            }
            try {
                if ($external->trashed()) {
                    $external->restore();
                    $message = 'Externe activé avec succès.';
                } else {
                    $external->delete();
                    $message = 'Externe désactivé avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for external ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }

    /**
     * **FIX: New method to delete attachments by their IDs**
     *
     * @param array $attachmentIds
     * @return void
     */
    public function deleteAttachments(array $attachmentIds): void
    {
        if (empty($attachmentIds)) {
            return;
        }

        $attachments = ExternalAttachment::whereIn('id', $attachmentIds)->get();
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
    }
}
