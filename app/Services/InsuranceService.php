<?php

namespace App\Services;

use App\Models\Insurance;
use App\Repositories\InsuranceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class InsuranceService
{
    private $repository;

    public function __construct(InsuranceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFiltered(array $params): LengthAwarePaginator
    {
        return $this->repository->getFiltered($params);
    }
    public function create(array $data)
    {
        return $this->repository->create($data);
    }
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }
    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }
    public function restore(int $id): Insurance
    {
        return $this->repository->restore($id);
    }
}