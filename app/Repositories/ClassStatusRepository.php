<?php

namespace App\Repositories;

use App\Models\ClassStatus;

class ClassStatusRepository
{
    public function all()
    {
        return ClassStatus::query()->get();
    }

    public function show(string $statusId): ClassStatus
    {
        return ClassStatus::query()->findOrFail($statusId);
    }

    public function create(array $data): ClassStatus
    {
        return ClassStatus::create($data);
    }

    public function update($id, array $data): bool
    {
        $classStatus = ClassStatus::findOrFail($id);
        return $classStatus->update($data);
    }

    public function delete($id): bool
    {
        $classStatus = ClassStatus::findOrFail($id);
        return $classStatus->delete();
    }

    public function restore(string $statusId): bool
    {
        $classStatus = ClassStatus::onlyTrashed()->findOrFail($statusId);
        return $classStatus->restore();
    }

    public function bulkDelete(array $ids): int
    {
        return ClassStatus::whereIn('id', $ids)->delete();
    }
}
