<?php

namespace App\Repositories;

use App\Models\Cycle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class CycleRepository
{
    /**
     *
     * @return Collection<int, Cycle>
     */
    public function getAll(): Collection
    {
        try {
            return Cycle::with('creator')->get();
        } catch (Exception $e) {
            Log::error('Error fetching all cycles: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Cycle>
     */
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        try {
            $query = Cycle::query()->with('creator');

            if (isset($filters['name']) && $filters['name'] !== '') {
                $searchTerm = $filters['name'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('code', 'like', '%' . $searchTerm . '%')
                      ->orWhere('cycle_id', 'like', '%' . $searchTerm . '%');
                });
            }
            if (isset($filters['code']) && $filters['code'] !== '') {
                $query->where('code', $filters['code']);
            }
            if (isset($filters['created_by']) && $filters['created_by'] !== '') {
                $query->where('created_by', $filters['created_by']);
            }

            if (isset($filters['activation_status']) && $filters['activation_status'] !== '') {
                switch ($filters['activation_status']) {
                    case 'active':
                        $query;
                        break;
                    case 'inactive':
                        $query->onlyTrashed();
                        break;
                    case 'all':
                        $query->withTrashed();
                        break;
                }
            }

            if (isset($filters['sort_by']) && isset($filters['sort_direction'])) {
                $query->orderBy($filters['sort_by'], $filters['sort_direction']);
            } else {
                $query->orderBy('order', 'asc');
            }

            Log::info('Cycle Query SQL: ' . $query->toSql());
            Log::info('Cycle Query Bindings: ' . json_encode($query->getBindings()));

            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error paginating cycles with filters: ' . $e->getMessage());
            return Cycle::whereRaw('1=0')->paginate($perPage);
        }
    }

    /**
     *
     * @param int $id
     * @return Cycle|null
     */
    public function find(int $id): ?Cycle
    {
        try {
            return Cycle::withTrashed()->with('creator')->find($id);
        } catch (Exception $e) {
            Log::error("Error finding cycle by ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return Cycle|null
     */
    public function create(array $data): ?Cycle
    {
        try {
            return Cycle::create($data);
        } catch (Exception $e) {
            Log::error('Error creating cycle: ' . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Cycle|null 
     */
    public function update(int $id, array $data): ?Cycle
    {
        try {
            $cycle = Cycle::withTrashed()->find($id);
            if ($cycle) {
                $cycle->update($data);
                return $cycle->fresh();
            }
            return null;
        } catch (Exception $e) {
            Log::error("Error updating cycle ID {$id}: " . $e->getMessage());
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
            $cycle = $this->find($id);
            if ($cycle) {
                return $cycle->delete();
            }
            return false;
        } catch (Exception $e) {
            Log::error("Error deleting cycle ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param array<int> $ids
     * @return bool
     */
    public function bulkDelete(array $ids): bool
    {
        $success = false;
        foreach ($ids as $id) {
            try {
                $cycle = Cycle::withTrashed()->find($id);
                if ($cycle) {
                    if ($cycle->trashed()) {
                        $cycle->restore();
                    } else {
                        $cycle->delete();
                    }
                    $success = true;
                }
            } catch (Exception $e) {
                Log::error("Error toggling status for cycle ID {$id}: " . $e->getMessage());
            }
        }
        return $success;
    }
}
