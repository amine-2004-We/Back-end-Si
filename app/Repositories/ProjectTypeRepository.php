<?php

namespace App\Repositories;

use App\Models\ProjectType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * class ProjectTypeRepository
 */
class ProjectTypeRepository
{
    /**
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = ProjectType::query()
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
    public function allTypes(): Collection
    {
        return ProjectType::all('id', 'name');
    }

    /**
     * @param int $id
     * @return ProjectType
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProjectType
    {
        return ProjectType::withTrashed()->findOrFail($id);
    }

    /**
     * @return Collection
     */
    public function getAllWithoutPagination(): Collection
    {
        return ProjectType::all('id', 'name');
    }

    /**
     * @param array $data
     * @return ProjectType
     */
    public function create(array $data): ProjectType
    {
        return ProjectType::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return ProjectType
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): ProjectType
    {
        $type = $this->find($id);
        $type->update($data);
        return $type;
    }

    /**
     * @param int $id
     * @return int
     * @throws ModelNotFoundException
     */
    public function delete(int $id): int
    {
        $type = $this->find($id);
        return $type->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return ProjectType::whereIn('id', $ids)->delete();
    }

    public function restore(int $id): ProjectType
    {
        $type = ProjectType::onlyTrashed()->findOrFail($id);

        $exists = ProjectType::where('name',$type->name)
            ->whereNull('deleted_at')
            ->exists();
        
        if($exists){
            abort(Response::HTTP_CONFLICT,'Ce type de projet existe deja');
        }

        $type->restore();
        
        return $type;
    }
}
