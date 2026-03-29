<?php

namespace App\Repositories;

use App\Models\ClassResource;
use App\Models\ProjectClass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClassRepository
{
    /**
     * Retrieve all classes.
     */
    public function all(): Collection
    {
        return ProjectClass::all();
    }

    /**
     * Retrieve classes with pagination and optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllClasses(array $filters = []): LengthAwarePaginator
    {
        $query = ProjectClass::with('classResources.project', 'perpetuationProject', 'transferToProject', 'relocateToClass', 'cycles', 'classStatus','douar.commune.province','unit.site');

        // Search filters
        if (!empty($filters['class_name'])) {
            $query->where('class_name', 'like', '%' . $filters['class_name'] . '%');
        }

        if (!empty($filters['class_code'])) {
            $query->where('class_code', 'like', '%' . $filters['class_code'] . '%');
        }


        if (!empty($filters['class_status_id'])) {
            $query->where('class_status_id', $filters['class_status_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a class by ID or fail.
     */
    public function findOrFail(int $id): ProjectClass
    {
        return ProjectClass::with('classResources.project', 'perpetuationProject', 'transferToProject', 'relocateToClass', 'cycles', 'classStatus')->findOrFail($id);
    }

    /**
     * Find a class by ID or return null.
     */
    public function find(int $id): ?ProjectClass
    {
        return ProjectClass::with('classResources.project', 'perpetuationProject', 'transferToProject', 'relocateToClass',  'cycles', 'classStatus')->find($id);
    }
    public function createWithAssociations(array $data, ?array $classResources = null): ProjectClass
    {
        return DB::transaction(function () use ($data, $classResources) {
            $cleanData = collect($data)->except('class_resources')->toArray();
            $class = ProjectClass::create($cleanData);
            if (!empty($classResources)) {
                $this->syncClassResources($class, $classResources);
            }

            return $class;
        });
    }

    protected function syncClassResources(ProjectClass $class, array $classResources): void
    {
        foreach ($classResources as $resourceData) {
            $class->classResources()->create([
                'class_id'=>$class->id,
                'project_id' => $resourceData['project_id'] ?? null,
                'collaborator_id' => $resourceData['collaborator_id'] ?? null,
                'start_date' => $resourceData['start_date'] ?? null,
                'end_date' => $resourceData['end_date'] ?? null,
                'isFavorite' => $resourceData['isFavorite'] ?? false,
            ]);
        }
    }

    /**
     * Create a new class.
     */
    public function create(array $data): ProjectClass
    {
        return ProjectClass::create($data);
    }

    /**
     * Update a class by ID.
     */
    public function update(int $id, array $data): ProjectClass
    {
        $class = $this->findOrFail($id);

        if (isset($data['class_resources']) && is_array($data['class_resources'])) {

            foreach ($data['class_resources'] as $resourceData) {

                $existingResources = ClassResource::where('class_id', $class->id)
                    ->where('collaborator_id', $resourceData['collaborator_id'])
                    ->where('isStill', true)
                    ->get();

                $projectChanged = true;

                foreach ($existingResources as $existing) {
                    if ($existing->project_id == $resourceData['project_id']) {
                        $existing->update([
                            'start_date' => $resourceData['start_date'] ?? $existing->start_date,
                            'end_date'   => $resourceData['end_date'] ?? $existing->end_date,
                            'isFavorite' => $resourceData['isFavorite'] ?? $existing->isFavorite,
                            'isStill'    => $resourceData['isStill'] ?? $existing->isStill,
                        ]);

                        $projectChanged = false;
                    } else {
                        $existing->update(['isStill' => false]);
                    }
                }

                if ($projectChanged) {
                    ClassResource::create([
                        'class_id'        => $class->id,
                        'project_id'      => $resourceData['project_id'],
                        'collaborator_id' => $resourceData['collaborator_id'],
                        'start_date'      => $resourceData['start_date'] ?? null,
                        'end_date'        => $resourceData['end_date'] ?? null,
                        'isStill'         => true,
                        'isFavorite'      => $resourceData['isFavorite'] ?? false,
                    ]);
                }
            }


            unset($data['class_resources']);
        }

        $class->update($data);

        return $class;
    }


    /**
     * Delete a class by ID.
     */
    public function delete(int $id): bool
    {
        $class = $this->findOrFail($id);
        return $class->delete();
    }

    /**
     * Retrieve only soft deleted classes with optional filters.
     */
    public function getAllTrashed(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = ProjectClass::onlyTrashed();

        if (!empty($filters['class_name'])) {
            $query->where('class_name', 'like', '%' . $filters['class_name'] . '%');
        }

        // Add other filters if needed...

        $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Find a soft-deleted class by ID.
     */
    public function findTrashedById(int $id): ?ProjectClass
    {
        return ProjectClass::onlyTrashed()->find($id);
    }

    /**
     * Restore a soft-deleted class.
     */
    public function restore(ProjectClass $class): bool
    {
        return $class->restore();
    }

    /**
     * Bulk delete classes by IDs.
     */
    public function bulkDelete(array $ids): int
    {
        return ProjectClass::whereIn('id', $ids)->delete();
    }

    /***
     * @param int $classId
     * @return mixed
     */
    public function findByClassId(int $classId)
    {
        return ClassResource::where('class_id', $classId)
            ->where('isStill', true)
            ->get();
    }
    public function findByClassIdArchive(int $classId)
    {
        return ClassResource::where('class_id', $classId)
            ->where('isStill', false)
            ->get();
    }
}
