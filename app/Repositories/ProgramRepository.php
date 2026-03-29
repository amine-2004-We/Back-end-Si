<?php

namespace App\Repositories;

use App\Models\Program;
use App\Models\ProgramType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ProgramRepository
{
    /**
     * @var array
     */
    public array $defaultWith = [
        'interventionAxis',
        'operationalManager',
        'pedagogicalManager',
        'regionalManager',
        'supervisor',
        'creator',
        'programTypes'
    ];

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = Program::query()->withTrashed();

            if (!empty($filters['search'])) {
                $query->where(function (Builder $q) use ($filters) {
                    $q->where('title', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                });
            }
            
           
            
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            if (!empty($filters['intervention_axis_id'])) {
                $query->where('intervention_axis_id', $filters['intervention_axis_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['activation_status'])) {
                if ($filters['activation_status'] === 'active') {
                    $query->whereNull('deleted_at');
                } elseif ($filters['activation_status'] === 'deactivated') {
                    $query->whereNotNull('deleted_at');
                }
            }
            
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';

            $query->orderBy($sortBy, $sortDirection)->with($this->defaultWith);
            
            return $query->paginate($perPage);

        } catch (Exception $e) {
            Log::error('Error paginating programs: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * @param array $data
     * @return Program|null
     */
    public function create(array $data): ?Program
    {
        try {
            return Program::create($data);
        } catch (Exception $e) {
            Log::error('Error creating program: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param Program $program
     * @param array $data
     * @return bool
     */
    public function update(Program $program, array $data): bool
    {
        try {
            return $program->update($data);
        } catch (Exception $e) {
            Log::error("Error updating program ID {$program->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int[] $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $program = Program::withTrashed()->find($id);
            if (!$program) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Programme non trouvé.'];
                continue;
            }
            try {
                if ($program->trashed()) {
                    $program->restore();
                    $message = 'Programme réactivé avec succès.';
                } else {
                    $program->delete();
                    $message = 'Programme désactivé avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for program ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur lors du changement de statut.'];
            }
        }
        return $results;
    }

    public function find(int $id): Program
    {
        return Program::withTrashed()->findOrFail($id);
    }

    public function getByInterventionAxis(int $axisId): Collection
    {
        return Program::where('intervention_axis_id', $axisId)
            ->with($this->defaultWith)
            ->get();
    }

    public function getProgramTypes(int $programId): Collection
    {
        return ProgramType::where('program_id', $programId)
            ->get();
    }
}