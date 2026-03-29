<?php

namespace App\Repositories;

use App\Models\Phase;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PhaseRepository
{
    public array $defaultWith = [ 'creator', 'tasks'];

    /**
     * Summary of getPaginated
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Phase::query()
            ->with($this->defaultWith)
            ->withTrashed()
            ->where('tag', 'project');

        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('name', 'like', '%' . $value . '%')
                            ->orWhere('phase_identifier', 'like', '%' . $value . '%');
                    });
                    break;

                case 'status':
                    $query->where('status', $value);
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

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (($filters['activation_status'] ?? null) === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        return $query->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Summary of findById
     * @param int $id
     * @return Builder<Phase>|Phase
     */
    public function findById(int $id): ?Phase
    {
        return Phase::withTrashed()->with($this->defaultWith)->find($id);
    }


    /**
     * Summary of create
     * @param array $data
     * @return Phase
     */
    public function create(array $data): Phase
    {
        return Phase::create($data);
    }

    /**
     * Summary of update
     * @param \App\Models\Phase $phase
     * @param array $data
     * @return bool
     */
    public function update(Phase $phase, array $data): bool
    {
        return $phase->update($data);
    }

    /**
     * Summary of delete
     * @param \App\Models\Phase $phase
     * @return bool|null
     */
    public function delete(Phase $phase): ?bool
    {
        return $phase->delete();
    }

    /**
     * Summary of toggleActivation
     * @param array $ids
     * @return array{id: mixed, message: string, success: bool[]}
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $phase = Phase::withTrashed()->find($id);
            if (!$phase) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Phase non trouvée.'];
                continue;
            }

            try {
                if ($phase->trashed()) {
                    $phase->restore();
                    $message = 'Phase activée avec succès.';
                } else {
                    $phase->delete();
                    $message = 'Phase désactivée avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for phase ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
            }
        }
        return $results;
    }
    public function getPaginatedByTag(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query= Phase::with($this->defaultWith)
            ->withTrashed()
            ->where('tag', true);

        foreach ($filters as $key => $value) {
            if (empty($value) || $value === 'all') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('name', 'like', '%' . $value . '%')
                            ->orWhere('phase_identifier', 'like', '%' . $value . '%');
                    });
                    break;
                case 'status':
                    $query->where($key, $value);
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

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (isset($filters['activation_status']) && $filters['activation_status'] === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

}
