<?php

namespace App\Services;

use App\Models\ProgramType;
use App\Repositories\ProgramTypeRepository;

class ProgramTypeService
{
    protected ProgramTypeRepository $programTypeRepository;

    public function __construct(ProgramTypeRepository $programTypeRepository)
    {
        $this->programTypeRepository = $programTypeRepository;
    }

    public function getAll($request)
    {
        return $this->programTypeRepository->getAll($request);
    }

    public function getById(int $id): ?ProgramType
    {
        return $this->programTypeRepository->findById($id);
    }
    public function create(array $data): ?ProgramType
    {
        return $this->programTypeRepository->create($data);
    }
    public function update(array $data, int $id): ?ProgramType
    {
        return $this->programTypeRepository->update($data, $id);
    }
    public function delete(int $id): ?ProgramType
    {
        return $this->programTypeRepository->delete($id);
    }

    public function restore(int $id): ?ProgramType
    {
        return $this->programTypeRepository->restore($id);
    }
}