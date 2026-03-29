<?php 
namespace App\Repositories;

use App\Models\Calltender;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CalltenderRepository
{
    /**
     * Get filtered call tenders based on parameters.
     *
     * @param array $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredCalltenders(array $params): LengthAwarePaginator|Collection
    {
       // 1. Initialize the query based on the status FIRST.
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Calltender::onlyTrashed();
        } else {
            $query = Calltender::query();
        }

        // Handle Global Search
        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('supplier', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('subject', 'ilike', '%' . $searchTerm . '%');
            });
        }

        // Handle Specific Column Filters
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        // Handle Sorting
        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        }
         else{
            $query->orderBy('created_at', 'desc'); 
        }

        // Handle Pagination
        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }
    /**
     * Create a new call tender.
     *
     * @param array $data
     * @return Calltender
     */
    public function create(array $data): Calltender 
    {
    return Calltender::create($data);
    }
    /**
     * Find a call tender by its ID.
     *
     * @param int $id
     * @return Calltender|null
     */
    public function find(int $id): ?Calltender
    {
        return Calltender::find($id);
    }
    /**
     * Update a call tender by its ID.
     *
     * @param int $calltender_id
     * @param array $data
     * @return Calltender
     */
    public function update(int $id, array $data): Calltender
    {
        $calltender = $this->find($id);
        if (!$calltender) {
            throw new ConflictHttpException('Call tender not found');
        }
        $calltender->update($data);
        return $calltender;
    }
    /**
     * Delete a call tender by its ID.
     *
     * @param int $calltender_id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $calltender = $this->find($id);
        if (!$calltender) {
            throw new ConflictHttpException('marche non trouvé');
        }

        return $calltender->delete();
    }
    /**
     * Restore a soft-deleted call tender by its ID.
     *
     * @param int $calltender_id
     * @return Calltender
     */
    public function restore(int $calltender_id): Calltender
    {
        $calltender = Calltender::withTrashed()->find($calltender_id);
        if (!$calltender) {
            throw new ConflictHttpException('Call tender not found');
        }
        $calltender->restore();
        return $calltender;
    }
    /**
     * Bulk delete call tenders by their IDs.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        $calltenders = Calltender::whereIn('id', $ids)->get();
        if ($calltenders->isEmpty()) {
            throw new ConflictHttpException('No call tenders found for the provided IDs');
        }
        
        foreach ($calltenders as $calltender) {
            $calltender->delete();
        }
        
        return $calltenders->count();
    }



}