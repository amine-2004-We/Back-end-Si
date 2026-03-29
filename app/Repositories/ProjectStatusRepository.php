<?php

namespace App\Repositories;

use App\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class ProjectStatusRepository
 */
class ProjectStatusRepository
{
    /**
     * @return LengthAwarePaginator
     */
    public function all(array $filters): LengthAwarePaginator
    {
        $query = ProjectStatus::query()
        ->orderBy('created_at', 'desc')
        ->orderByRaw('deleted_at IS NOT NULL');

        if(!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        }else{
            $query->withoutTrashed();
        }

        if(!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return ProjectStatus::all('id', 'name');
    }

    /**
     * @param int $id
     * @return ProjectStatus
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectStatus
    {
        return ProjectStatus::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return ProjectStatus
     */
    public function create(array $data): ProjectStatus
    {
        return ProjectStatus::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return ProjectStatus
     * @throws ModelNotFoundException
     */
    public function update($id, array $data): ProjectStatus
    {
        $status = $this->find($id);
        $status->update($data);
        return $status;
    }

    /**
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $status = $this->find($id);
        return $status->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return ProjectStatus::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return ProjectStatus|Response
     * @throws ModelNotFoundException
     */
    public function restore(int $id)
    {
        $status = ProjectStatus::withTrashed()->findOrFail($id);
        $exists = ProjectStatus::where('name', $status->name)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            abort(Response::HTTP_CONFLICT, 'Ce statut de projet existe deja');
        }

        $status->restore();

        return $status;
    }
}
