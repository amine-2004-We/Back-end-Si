<?php

namespace App\Repositories;

use App\Models\ExternalTrainer;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ExternalTrainerRepository
{
    /**
     * Default relations to load with the model.
     *
     * @var array
     */
    public array $defaultWith = ['cabinet', 'creator', 'trainer'];

    /**
     * Get a paginated list of external trainers with filtering and sorting.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ExternalTrainer::query()->with($this->defaultWith)->withTrashed();

        // Apply filters
        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $query->where(function (Builder $q) use ($value) {
                        $q->whereHas('trainer', function (Builder $trainerQuery) use ($value) {
                            $trainerQuery->where('full_name', 'like', '%' . $value . '%')
                                         ->orWhere('email', 'like', '%' . $value . '%');
                        })
                        ->orWhere('trainer_identifier', 'like', '%' . $value . '%');
                    });
                    break;

                case 'cabinet_id':
                    $query->where('cabinet_id', $value);
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

        // Apply sorting
        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
                     ->paginate($perPage);
    }

    /**
     * Find an external trainer by their ID.
     *
     * @param int $id
     * @return ExternalTrainer|null
     */
    public function findById(int $id): ?ExternalTrainer
    {
        return ExternalTrainer::withTrashed()->with($this->defaultWith)->find($id);
    }

    /**
     * Create a new external trainer record.
     *
     * @param array $data
     * @return ExternalTrainer
     */
    public function create(array $data): ExternalTrainer
    {
        return ExternalTrainer::create($data);
    }

    /**
     * Update an existing external trainer record.
     *
     * @param ExternalTrainer $trainer
     * @param array $data
     * @return bool
     */
    public function update(ExternalTrainer $trainer, array $data): bool
    {
        return $trainer->update($data);
    }

    /**
     * Soft delete an external trainer record.
     *
     * @param ExternalTrainer $trainer
     * @return bool|null
     */
    public function delete(ExternalTrainer $trainer): ?bool
    {
        return $trainer->delete();
    }

    /**
     * Toggle the activation status (active/inactive) for a list of trainers.
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $trainer = ExternalTrainer::withTrashed()->find($id);
            if (!$trainer) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Formateur externe non trouvé.'];
                continue;
            }
            try {
                if ($trainer->trashed()) {
                    $trainer->restore();
                    $message = 'Formateur externe activé avec succès.';
                } else {
                    $trainer->delete();
                    $message = 'Formateur externe désactivé avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for external trainer ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }
}
