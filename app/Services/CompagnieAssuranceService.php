<?php

namespace App\Services;

use App\Models\CompagnieAssurance;
use App\Repositories\CompagnieAssuranceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompagnieAssuranceService
{
    public function __construct(public CompagnieAssuranceRepository $repository) {}

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function find(int $id): ?CompagnieAssurance
    {
        return $this->repository->find($id);
    }

    public function create(array $data): CompagnieAssurance
    {
        return $this->repository->create($data);
    }

    public function update(CompagnieAssurance $compagnie, array $data): CompagnieAssurance
    {
        return DB::transaction(function () use ($compagnie, $data) {
            $this->repository->update($compagnie, $data);
            return $compagnie->fresh();
        });
    }

    public function toggleActivation(array $ids): array
    {
        return $this->repository->toggleActivation($ids);
    }
}
