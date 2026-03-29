<?php

namespace App\Repositories;

use App\Models\CallForProject;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class CallForProjectRepository {

    private $callForProject;

    public function __construct(CallForProject $callForProject)
    {
        $this->callForProject = $callForProject;
    }


    public function getFilteredAll(array $params) : LengthAwarePaginator
    {
           if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = CallForProject::onlyTrashed();
        } else {
            $query = CallForProject::query();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
        
    }

    public function create(array $data): CallForProject
    {
        return $this->callForProject->create($data);
    }

    public function update(int $id, array $data): CallForProject
    {
        $callForProject = $this->callForProject->find($id);
        $callForProject->update($data);
        return $callForProject;
    }

    public function delete(int $id): void
    {
        $this->callForProject->find($id)->delete();
    }

    public function showCallForProject(int $id): CallForProject
    {
        return $this->callForProject->find($id);
    }

    public function restore(int $id): CallForProject
    {
        $callForProject = $this->callForProject->onlyTrashed()->find($id);
        $callForProject->restore();

        return $callForProject;
    }

    public function bulkDelete(array $ids): void
    {
        $this->callForProject->whereIn('id', $ids)->delete();
    }


      public function updateStatus(CallForProject $callForProject, string $status): void
    {
        $callForProject->status = $status;
        $callForProject->save();
    }
}


