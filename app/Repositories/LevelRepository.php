<?php

namespace App\Repositories;

use App\Models\Level;
use App\Models\Cycle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * class LevelRepository
 */
class LevelRepository
{
    /**
     *
     * @return Collection<int, Level>
     */
    public function getAll(): Collection
    {
        try {
            return Level::with('cycle', 'creator')->get();
        } catch (Exception $e) {
            Log::error('Error fetching all levels: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Level>
     */
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        try {
            $query = Level::query()->with('cycle', 'creator');
            if (isset($filters['with_trashed']) && $filters['with_trashed']) {
                $query->withTrashed();
            }

            if (isset($filters['name']) && $filters['name'] !== '') {
                $searchTerm = $filters['name'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('code', 'like', '%' . $searchTerm . '%')
                      ->orWhere('level_id', 'like', '%' . $searchTerm . '%');
                });
            }
            if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
                if ($filters['status'] === 'Actif') {
                    $query->whereNull('deleted_at');
                } elseif ($filters['status'] === 'Inactif') {
                    $query->whereNotNull('deleted_at');
                }
            }

            if (isset($filters['cycle_id']) && $filters['cycle_id'] !== '') {
                $query->where('cycle_id', $filters['cycle_id']);
            }

            if (isset($filters['created_by']) && $filters['created_by'] !== '') {
                $query->where('created_by', $filters['created_by']);
            }

            if (isset($filters['sort_by']) && isset($filters['sort_direction'])) {
                $query->orderBy($filters['sort_by'], $filters['sort_direction']);
            } else {
                $query->orderBy('order', 'asc');
            }
            return $query->withTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error paginating levels with filters: ' . $e->getMessage());
            return Level::whereRaw('1=0')->paginate($perPage);
        }
    }

    /**
     *
     * @param int $id
     * @return Level|null
     */
    public function find(int $id): ?Level
    {
        try {
            return Level::withTrashed()->with('cycle', 'creator')->find($id);
        } catch (Exception $e) {
            Log::error("Error finding level by ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return Level|null
     */
    public function create(array $data): ?Level
    {
        try {
            return Level::create($data);
        } catch (Exception $e) {
            Log::error('Error creating level: ' . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Level|null
     */
    public function update(int $id, array $data): ?Level
    {
        try {
            $level = Level::withTrashed()->find($id);
            if ($level) {
                $level->update($data);
                return $level->fresh();
            }
            return null;
        } catch (Exception $e) {
            Log::error("Error updating level ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $level = $this->find($id);
            if ($level) {
                return $level->delete();
            }
            return false;
        } catch (Exception $e) {
            Log::error("Error deleting level ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param array<int> $ids
     * @return bool
     */
    public function bulkToggleStatus(array $ids): bool
    {
        $success = false;
        foreach ($ids as $id) {
            try {
                $level = Level::withTrashed()->find($id);
                if ($level) {
                    if ($level->trashed()) {
                        $level->restore();
                    } else {
                        $level->delete();
                    }
                    $success = true;
                }
            } catch (Exception $e) {
                Log::error("Error toggling status for level ID {$id}: " . $e->getMessage());
            }
        }
        return $success;
    }

    /**
     *
     * @return array
     */
    public function getFormOptions(): array
    {
        try {
            $cycles = Cycle::all();
            return [
                'cycles' => $cycles->map(function ($cycle) {
                    return [
                        'value' => $cycle->id,
                        'label' => $cycle->title . ' (' . $cycle->code . ')',
                    ];
                })->toArray(),
            ];
        } catch (Exception $e) {
            Log::error('Error fetching level form options: ' . $e->getMessage());
            return ['cycles' => []];
        }
    }
}
