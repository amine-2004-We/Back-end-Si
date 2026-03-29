<?php

namespace App\Repositories;

use App\Models\ClassTypes;

class ClassTypeRepository
{
    public function all()
    {
        return ClassTypes::query()->get();
    }
    public function show(string $leaveTypeId): ClassTypes
    {
        return ClassTypes::query()->findOrFail($leaveTypeId);
    }

    public function create($data): ClassTypes
    {
        return ClassTypes::create($data);
    }
    public function update($id, $data): ClassTypes
    {
        $classType=ClassTypes::findOrFail($id);
        $classType->update($data);
        return $classType;
    }
    public function delete($id): bool
    {
        $classType=ClassTypes::findOrFail($id);
        return $classType->delete();
    }
    public function restore(string $classTypeId): bool
    {
        $classTypeId = ClassTypes::onlyTrashed()->findOrFail($classTypeId);
        return $classTypeId->restore();

    }
    public function bulkDelete(array $ids): int
    {
        return ClassTypes::whereIn('id', $ids)->delete();
    }

}
