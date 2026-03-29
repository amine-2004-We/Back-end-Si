<?php

namespace App\Repositories;

use App\Models\GroupType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * class GroupTypeRepository
 */
class GroupTypeRepository
{
    /**
     * @return mixed
     */
    public function all()
    {
        return GroupType::paginate(10);
    }

    /**
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function withFilters(array $filters) {
        $query = GroupType::query()
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            } else {
                $query->withTrashed();
            }
        } else {
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
        return GroupType::all('id','name');
    }

    /**
     * @param int $id
     * @return GroupType
     */
    public function find(int $id): GroupType
    {
        return GroupType::find($id);
    }

    public function findWithTrashed(int $id): GroupType
    {
        return GroupType::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return GroupType
     */
    public function create(array $data): GroupType
    {
        $groupType = new GroupType();
        $groupType->fill($data);
        $groupType->save();
        return $groupType;
    }

    /**
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        $groupType = $this->find($id);
        $groupType->update($data);
        return $groupType;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        $groupType = $this->find($id);
        $groupType->delete();
        return $groupType;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return GroupType::destroy($ids['group_type_ids']);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(int $id): mixed
    {
        $groupType = GroupType::withTrashed()->find($id);

        if (!$groupType) {
            throw new ModelNotFoundException("Type de groupe non trouvé.");
        }

        $nameExists = GroupType::where('name', $groupType->name)
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->exists();

        if ($nameExists) {
            $message = 'Impossible de restaurer : le nom est déjà utilisé.';
            abort(Response::HTTP_CONFLICT, $message);
        }

        $groupType->restore();

        return $groupType;
    }
}
