<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * class GroupRepository
 */
class GroupRepository
{
    /**
     * @return mixed
     */
    public function all(): mixed
    {
        return Group::paginate(10);
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator|mixed
     */
    public function withFilters(array $filters): mixed
    {
        $query = Group::query()
        ->with('class',  'educator','creator')
        ->orderByRaw('deleted_at IS NOT NULL')
        ->orderByDesc('created_at');

        if (!empty($filters['group_id'])) {
        $query->where('id', '=', $filters['group_id']);
        }

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

        if(!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
        }

        if(!empty($filters['class_id'])) {
            $query->where('class_id', '=', $filters['class_id']);
        }

        if(!empty($filters['group_type_id'])) {
            $query->where('group_type_id', '=', $filters['group_type_id']);
        }

        if(!empty($filters['educator_id'])) {
            $query->where('educator_id', '=', $filters['educator_id']);
        }

        if(!empty($filters['status'])) {
            $query->where('status', '=', $filters['status']);
        }

        if(!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if(!empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        $perPage = !empty($filters['per_page']) ? $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @return Collection
     */
    public function allWithoutPagination(): Collection
    {
        return Group::all('id', 'name', 'code', 'status');
    }

    /**
     * @param string $id
     * @return Group
     */
    public function find(string $id): Group
    {
        return Group::find($id);
    }

    /**
     * @param string $id
     * @return Group
     */
    public function findWithTrashed(string $id): Group
    {
        return Group::withTrashed()->find($id);
    }

    /**
     * @param array $data
     * @return Group
     */
    public function create(array $data): Group
    {
        $group = new Group();
        $group->fill($data);
        $group->save();
        $this->attachGroupToClassTasks($group);

        DB::commit();
        return $group;
    }

    /***
     * @param Group $group
     * @return void
     */
    private function attachGroupToClassTasks(Group $group): void
    {
        $taskIds = DB::table('tasks')
            ->where('class_id', $group->class_id)
            ->pluck('id')
            ->toArray();

        if (empty($taskIds)) {
            return;
        }

        $rows = array_map(function ($taskId) use ($group) {
            return [
                'task_id' => $taskId,
                'group_id' => $group->id,
            ];
        }, $taskIds);

        DB::table('task_group')->insert($rows);
    }

    /**
     * @param array $data
     * @param string $id
     * @return mixed
     */
    public function update(array $data, string $id): mixed
    {
        $group = $this->find($id);
        $group->update($data);
        return $group;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id): mixed
    {
        $group = $this->find($id);
        $group->delete();
        return $group;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete(array $ids): mixed
    {
        return Group::destroy($ids);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function restore(string $id): mixed
    {
        $group = $this->findWithTrashed($id);

        if (!$group) {
            abort(404, 'Group not found.');
        }

        $exists = Group::where('name', $group->name)->whereNull('deleted_at')->exists();

        if ($exists) {
            abort(409, 'Le nom du groupe est déjà utilisé.');
        }

        $group->restore();
        return $group;
    }
}
