<?php

namespace App\Repositories;

use App\Models\TrainingSession;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 *class TrainingSessionRepository
 */
class TrainingSessionRepository
{
    /**
     * @var array|string[]
     */
    public array $defaultWith = [
        'module', 'training', 'trainingGroup', 'animator', 'site', 'creator', 'attachments'
    ];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TrainingSession::query()->with($this->defaultWith)->withTrashed();

        $this->applyFilters($query, $filters);

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Helper method to apply filters and search to the query builder.
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('session_identifier', 'like', "%{$search}%")
                         ->orWhereHas('module', function (Builder $moduleQuery) use ($search) {
                             $moduleQuery->where('title', 'like', "%{$search}%");
                         });
            });
        });

        $query->when($filters['activation_status'] ?? 'active', function (Builder $q, $status) {
            if ($status === 'active') {
                $q->whereNull('deleted_at');
            } elseif ($status === 'deactivated') {
                $q->whereNotNull('deleted_at');
            }
        });

        $query->when($filters['module_id'] ?? null, function (Builder $q, $moduleId) {
            $q->where('module_id', $moduleId);
        });

        $query->when($filters['training_group_id'] ?? null, function (Builder $q, $groupId) {
            $q->where('training_group_id', $groupId);
        });
    }

    /**
     * @param int $id
     * @return TrainingSession|null
     */
    public function find(int $id): ?TrainingSession
    {
        return TrainingSession::with($this->defaultWith)->withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return TrainingSession
     */
    public function create(array $data): TrainingSession
    {
        return TrainingSession::create($data);
    }

    /**
     * @param TrainingSession $session
     * @param array $data
     * @return bool
     */
    public function update(TrainingSession $session, array $data): bool
    {
        return $session->update($data);
    }

    /**
     * Toggles the activation status of multiple sessions.
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        $sessions = TrainingSession::withTrashed()->whereIn('id', $ids)->get();

        foreach ($sessions as $session) {
            try {
                if ($session->trashed()) {
                    $session->restore();
                    $message = 'Séance réactivée avec succès.';
                } else {
                    $session->delete();
                    $message = 'Séance désactivée avec succès.';
                }
                $results[] = ['id' => $session->id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for session ID {$session->id}: " . $e->getMessage());
                $results[] = ['id' => $session->id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }
}
