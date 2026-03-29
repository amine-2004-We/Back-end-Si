<?php

namespace App\Repositories;

use App\Models\ProgrammePedagogique;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProgrammePedagogiqueRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = ProgrammePedagogique::with(['class', 'user', 'project']);

        // Filter by authenticated user
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('observation', 'like', '%' . $search . '%')
                  ->orWhere('id', $search);
            });
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['deleted_at'])) {
            $val = $filters['deleted_at'];
            if (is_string($val)) {
                $low = strtolower($val);
                if ($low === 'false' || $low === '0') {
                    $val = false;
                } elseif ($low === 'true' || $low === '1') {
                    $val = true;
                } elseif ($low === 'all') {
                    $val = 'all';
                }
            }

            if ($val === false) {
                $query->whereNull('deleted_at');
            } elseif ($val === true) {
                $query->whereNotNull('deleted_at');
            } elseif ($val === 'all') {
                $query->withTrashed();
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findOrFail(int $id): ProgrammePedagogique
    {
        return ProgrammePedagogique::with(['class', 'user', 'project'])->findOrFail($id);
    }

    public function find(int $id): ?ProgrammePedagogique
    {
        return ProgrammePedagogique::with(['class', 'user', 'project'])->find($id);
    }

    public function create(array $data): ProgrammePedagogique
    {
        return ProgrammePedagogique::create($data);
    }

    public function update(int $id, array $data): ProgrammePedagogique
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
        $query = ProgrammePedagogique::with(['class', 'user', 'project'])->withTrashed();

        // If caller specifically wants only trashed, apply whereNotNull
        if (isset($filters['only_trashed']) && $filters['only_trashed']) {
            $query->whereNotNull('deleted_at');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('observation', 'like', '%' . $search . '%')
                  ->orWhere('id', $search);
            });
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($perPage);
    }

    public function findTrashedById(int $id): ?ProgrammePedagogique
    {
        return ProgrammePedagogique::onlyTrashed()->find($id);
    }

    public function restore(ProgrammePedagogique $model): bool
    {
        return $model->restore();
    }

    public function bulkDelete(array $ids): int
    {
        return ProgrammePedagogique::whereIn('id', $ids)->delete();
    }
}
