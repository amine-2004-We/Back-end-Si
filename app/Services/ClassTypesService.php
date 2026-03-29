<?php

namespace App\Services;

use App\Http\Requests\StoreClassTypesRequest;
use App\Models\ClassTypes;
use App\Repositories\ClassTypeRepository;

class ClassTypesService
{
    protected ClassTypeRepository $classTypeRepository;

    public function __construct(ClassTypeRepository $classTypeRepository)
    {
        $this->classTypeRepository = $classTypeRepository;
    }

    public function all()
    {
        return $this->classTypeRepository->all();
    }

    public function show(string $typeId): ClassTypes
    {
        return $this->classTypeRepository->show($typeId);
    }

    public function create(StoreClassTypesRequest $request): ClassTypes
    {
        $data = $request->validated();
        return $this->classTypeRepository->create($data);
    }

    public function update(string $typeId, StoreClassTypesRequest $request): ClassTypes
    {
        $data = $request->validated();
        $this->classTypeRepository->update($typeId, $data);
        return $this->classTypeRepository->show($typeId);
    }

    public function delete(string $typeId): bool
    {
        return $this->classTypeRepository->delete($typeId);
    }

    public function restore(string $typeId): bool
    {
        return $this->classTypeRepository->restore($typeId);
    }

    public function bulkDelete(array $ids): bool
    {
        return ClassTypes::query()->whereIn('id', $ids)->delete();
    }
}
