<?php

namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;


class UnitRepository
{
    /**
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Unit>
     */

    protected $classRelations = [
        'classes',
       
        'classes.cycles',
        'classes.classStatus',
    ];
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Unit::query()->withTrashed();

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'ilike', $searchTerm);
                 
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'ilike', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        if (!empty($filters['educator_id'])) {
            $query->where('educator_id', $filters['educator_id']);
        }

        if (isset($filters['status_filter'])) {
            if ($filters['status_filter'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status_filter'] === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        } else {
            $query->whereNull('deleted_at');
        }


        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

         $query->with(array_merge([
            'site.commune.cercle.province.region.country',
            'site.douar',
            'educator',
            'creator',
        ], $this->classRelations));

        return $query->paginate($perPage);
    }

    /**
     * Find a unit by its ID.
     * Includes soft-deleted units in the search.
     *
     * @param int $id
     * @return Unit|null
     */
    public function findById(int $id): ?Unit
    {
        return Unit::withTrashed()->with(array_merge([
            'site.commune.cercle.province.region.country',
            'educator',
            'creator',
        ], $this->classRelations))->find($id);
    }


    /**
     *
     * @param array<string, mixed> $data
     * @return Unit
     */
    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    /**
     *
     * @param Unit $unit
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(Unit $unit, array $data): bool
    {
        return $unit->update($data);
    }

    /**
     *
     * @param Unit $unit
     * @return bool|null
     */
    public function delete(Unit $unit): ?bool
    {
        return $unit->delete();
    }

    /**
     *
     * @param array<int> $ids
     * @return array<array{id: int, success: bool, message: string}>
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $unit = Unit::withTrashed()->find($id);

            if (!$unit) {
                $results[] = [
                    'id' => $id,
                    'success' => false,
                    'message' => 'Unité non trouvée.'
                ];
                continue;
            }

            try {
                if ($unit->trashed()) {
                    $unit->restore();
                    $message = 'Unité restaurée avec succès.';
                } else {
                    $unit->delete();
                    $message = 'Unité désactivée avec succès.';
                }
                $results[] = [
                    'id' => $id,
                    'success' => true,
                    'message' => $message
                ];
            } catch (\Exception $e) {
                Log::error("Error toggling activation for unit ID {$id}: " . $e->getMessage());
                $results[] = [
                    'id' => $id,
                    'success' => false,
                    'message' => 'Erreur lors du changement de statut: ' . $e->getMessage()
                ];
            }
        }
        return $results;
    }

    /**
     *
     * @param int $siteId
     * @return string
     */
    public function generateUniqueUnitId(int $siteId): string
    {
        // Get the highest unit number for this site, including soft-deleted units
        $lastUnit = Unit::withTrashed()
            ->where('site_id', $siteId)
            ->where('unit_id', 'like', "SITE{$siteId}-UNIT%")
            ->orderByRaw('CAST(SUBSTRING(unit_id FROM \'-UNIT([0-9]+)$\') AS INTEGER) DESC')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;

        if ($lastUnit && $lastUnit->unit_id) {
            // Extract the number part from the unit_id
            if (preg_match('/SITE' . $siteId . '-UNIT(\d+)/', $lastUnit->unit_id, $matches)) {
                $lastNumber = (int)$matches[1];
                $nextNumber = $lastNumber + 1;
            }
        }

        // Keep trying until we find a unique ID (in case of race conditions)
        do {
            $unitId = "SITE{$siteId}-UNIT{$nextNumber}";
            $exists = Unit::withTrashed()->where('unit_id', $unitId)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $unitId;
    }

    /**
     *
     * @return Collection<int, Unit>
     */
    public function getAll(): Collection
    {
        return Unit::with([
            'site.commune.cercle.province.region.country',
            'educator',
            'creator',
            
        ])->get();
    }

    public function getFilteredUnits(int $regionId): Collection
    {
        return Unit::with([
            'site.commune.cercle.province.region.country',
        ])
            ->whereHas('site.commune.cercle.province.region', function ($query) use ($regionId) {
                $query->where('id', $regionId);
            })
            ->get();
    }
}
