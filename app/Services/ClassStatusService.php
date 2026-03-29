<?php

namespace App\Services;

use App\Http\Requests\StoreClassStatusRequest;
use App\Models\ClassStatus;
use App\Repositories\ClassStatusRepository;

class ClassStatusService
{
    protected ClassStatusRepository $classStatusRepository;

    public function __construct(ClassStatusRepository $classStatusRepository)
    {
        $this->classStatusRepository = $classStatusRepository;
    }

    public function all()
    {
        return $this->classStatusRepository->all();
    }

    public function show(string $statusId): ClassStatus
    {
        return $this->classStatusRepository->show($statusId);
    }

    public function create(StoreClassStatusRequest $request): ClassStatus
    {
        $data = $request->validated();
        return $this->classStatusRepository->create($data);
    }

    public function update(string $statusId, StoreClassStatusRequest $request): ClassStatus
    {
        $data = $request->validated();
        $this->classStatusRepository->update($statusId, $data);
        return $this->classStatusRepository->show($statusId); // return updated instance
    }

    public function delete(string $statusId): bool
    {
        return $this->classStatusRepository->delete($statusId);
    }

    public function restore(string $statusId): bool
    {
        return $this->classStatusRepository->restore($statusId);
    }

    public function bulkDelete(array $ids): bool
    {
        return ClassStatus::query()->whereIn('id', $ids)->delete();
    }
}
