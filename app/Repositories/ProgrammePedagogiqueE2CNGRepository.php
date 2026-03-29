<?php

namespace App\Repositories;

use App\Models\ProgrammePedagogiqueE2CNG;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProgrammePedagogiqueE2CNGRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = ProgrammePedagogiqueE2CNG::with(['groupe', 'project']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('project_pedagogique', 'ilike', '%' . $search . '%')
                  ->orWhere('metier', 'ilike', '%' . $search . '%')
                  ->orWhere('observation', 'ilike', '%' . $search . '%');
            });
        }

        if (!empty($filters['groupe_id'])) {
            $query->where('groupe_id', $filters['groupe_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['deleted_at'])) {
            $val = $filters['deleted_at'];
            if ($val === 'false') $query->whereNull('deleted_at');
            elseif ($val === 'true') $query->onlyTrashed();
            elseif ($val === 'all') $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }


    public function findOrFail(int $id): ProgrammePedagogiqueE2CNG
    {
        return ProgrammePedagogiqueE2CNG::with(['groupe', 'project'])->findOrFail($id);
    }

    public function find(int $id): ?ProgrammePedagogiqueE2CNG
    {
        return ProgrammePedagogiqueE2CNG::with(['groupe', 'project'])->find($id);
    }

    public function create(array $data): ProgrammePedagogiqueE2CNG
    {
        return ProgrammePedagogiqueE2CNG::create($data);
    }

    public function update(int $id, array $data): ProgrammePedagogiqueE2CNG
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function getAllTrashed(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = ProgrammePedagogiqueE2CNG::with(['groupe', 'project'])->withTrashed();

        // If caller specifically wants only trashed, apply whereNotNull
        if (isset($filters['only_trashed']) && $filters['only_trashed']) {
            $query->whereNotNull('deleted_at');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('project_pedagogique', 'like', '%' . $search . '%')
                  ->orWhere('metier', 'like', '%' . $search . '%')
                  ->orWhere('observation', 'like', '%' . $search . '%')
                  ->orWhere('id', $search);
            });
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($perPage);
    }

    public function findTrashedById(int $id): ?ProgrammePedagogiqueE2CNG
    {
        return ProgrammePedagogiqueE2CNG::onlyTrashed()->find($id);
    }

    public function restore(ProgrammePedagogiqueE2CNG $model): bool
    {
        return $model->restore();
    }

    public function bulkDelete(array $ids): int
    {
        return ProgrammePedagogiqueE2CNG::whereIn('id', $ids)->delete();
    }
}
