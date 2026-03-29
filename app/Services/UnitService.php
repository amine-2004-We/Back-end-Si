<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Repositories\ClassRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

/**
 * class UnitService.
 */
class UnitService
{
    /**
     * @var UnitRepository
     */
    protected UnitRepository $unitRepository;
    /**
     * @var ClassRepository
     */
    protected ClassRepository $classRepository;

    /**
     * @param UnitRepository $unitRepository
     * @param ClassRepository $classRepository
     */
    public function __construct(UnitRepository $unitRepository, ClassRepository $classRepository)
    {
        $this->unitRepository = $unitRepository;
        $this->classRepository = $classRepository;
    }

    /**
     * Summary of getAllUnits
     * @return Collection<int, Unit>
     */
    public function getAllUnits(): Collection
    {
        return $this->unitRepository->getAll();
    }

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUnits(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->unitRepository->getPaginated($filters, $perPage);
    }

    /**
     * @param int $id
     * @return Unit|null
     */
    public function getUnit(int $id): ?Unit
    {
        return $this->unitRepository->findById($id);
    }

    /**
     * Summary of createUnit
     * @param array $data
     */
    public function createUnit(array $data): ?Unit
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['site_id'])) {
                $site = Site::findOrFail($data['site_id']);
                $data['unit_id'] = $this->unitRepository->generateUniqueUnitId($site->id);
            }

            $classesData = Arr::pull($data, 'classes', []);

            $unit = $this->unitRepository->create($data);

            if (!empty($classesData)) {
                foreach ($classesData as $classData) {
                    $classData['unit_id'] = $unit->id;
                    $this->classRepository->create($classData);
                }
            }

            return $unit;
        });
    }

    /**
     * Summary of updateUnit
     * @param int $id
     * @param array $data
     */
    public function updateUnit(int $id, array $data): ?Unit
    {
        $unit = $this->unitRepository->findById($id);
        if (!$unit) {
            return null;
        }

        return DB::transaction(function () use ($unit, $data) {
            $classesData = Arr::pull($data, 'classes', []);

            $this->unitRepository->update($unit, $data);

            $incomingClassIds = collect($classesData)->pluck('id')->filter();
            $existingClassIds = $unit->classes()->pluck('id');
            $idsToDelete = $existingClassIds->diff($incomingClassIds);

            if ($idsToDelete->isNotEmpty()) {
                foreach ($idsToDelete as $classId) {
                    $this->classRepository->delete($classId);
                }
            }

            if (!empty($classesData)) {
                foreach ($classesData as $classData) {
                    if (isset($classData['id'])) {
                        // The ClassRepository's update method expects the ID and the data array
                        $classToUpdate = $this->classRepository->find($classData['id']);
                        if ($classToUpdate) {
                             $this->classRepository->update($classToUpdate->id, $classData);
                        }
                    } else {
                        $classData['unit_id'] = $unit->id;
                        $this->classRepository->create($classData);
                    }
                }
            }

            return $unit->fresh();
        });
    }

    /**
     * Summary of toggleUnitActivation
     * @param array $ids
     * @return array{id: int, message: string, success: bool[]}
     */
    public function toggleUnitActivation(array $ids): array
    {
        return $this->unitRepository->toggleActivation($ids);
    }

    /**
     * Summary of getFilteredUnits
     * @param int $regionId
     * @return Collection
     */
    public function getFilteredUnits(int $regionId): Collection
    {
        return $this->unitRepository->getFilteredUnits($regionId);
    }
}
