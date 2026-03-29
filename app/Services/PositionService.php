<?php

namespace App\Services;

use App\Models\Position;
use App\Repositories\PositionRepository;
use Illuminate\Support\Collection;

class PositionService
{
    protected PositionRepository $repository;

    public function __construct(PositionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getArchived(): Collection
    {
        return $this->repository->allWithTrashed();
    }

    public function find(string $id): Position
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Position
    {
        return $this->repository->store($data);
    }

    public function update(string $id, array $data): Position
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function restore(string $id): bool
    {
        return $this->repository->restore($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }
}
