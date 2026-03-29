<?php

namespace App\Repositories;

use App\Models\Grant;
 use Illuminate\Pagination\LengthAwarePaginator;

class GrantRepository
{
    /**
     * get paginated list of grants with all filtering and sorting.
     */

    public function getGrants(array $params): LengthAwarePaginator
    {
       if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Grant::onlyTrashed(); 
        } else {
            $query = Grant::query(); 
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->whereHas('partner', function ($q) use ($searchTerm) {
                $q->where('partner_name', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sortBy']) && !empty($params['sortOrder'])) {
            $query->orderBy($params['sortBy'], $params['sortOrder']);
        }
        else {
            $query->orderBy('created_at', 'desc');
        }
        $query->with(['partner', 'convention', 'project', 'bankAccount', 'user']);

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * create a new grant
     * @param array $data
     * @return Grant
     */
    public function create(array $data): Grant 
    {
        return Grant::create($data);
    }
    /**
     * update a grant
     * @param int $id
     * @param array $data
     * @return Grant|null
     */
    public function update(int $id, array $data): ?Grant 
    {
        $grant = Grant::withTrashed()->find($id);
        if ($grant) {
            $grant->update($data);
            return $grant;
        }
        return null;
      
    }
    /**
     * delete a grant
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool 
    {
        return Grant::destroy($id);
    }
    /**
     * restore a grant
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool 
    {
        return Grant::withTrashed()->find($id)->restore();
    }

    /**
     * bulk delete grants
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int 
    {
        return Grant::withTrashed()->whereIn('id', $ids)->forceDelete();
    }
    /**
     * show a grant
     * @param int $id
     * @return Grant|null
     */
    public function show(int $id): ?Grant
    {
        return Grant::withTrashed()->find($id);
    }
}