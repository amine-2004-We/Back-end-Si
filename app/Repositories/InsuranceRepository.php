<?php
namespace App\Repositories;
use App\Models\Insurance;
use Illuminate\Pagination\LengthAwarePaginator;


class InsuranceRepository
{
    /**
     * Get a paginated list of insurances with all filters and sorting.
     *
     * @param array $params The query parameters from the HTTP request.
     * @return LengthAwarePaginator
     */
        public function getFiltered(array $params): LengthAwarePaginator
        {
            // 1. Initialize the query based on the status FIRST.
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Insurance::onlyTrashed();
        } else {
            $query = Insurance::query();
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
         * Create a new insurance.
         *
         * @param array $data
         * @return Insurance
         */
        public function create(array $data): Insurance 
        {
        return Insurance::create($data);
        }
        /**
         * Get a specific insurance by ID.
         *
         * @param int $id
         * @return Insurance
         *
         * @throws ModelNotFoundException
         */
        public function find(int $id): Insurance
        {
        return Insurance::withTrashed()->findOrFail($id);
        }
        /**
         * Update an insurance by its ID.
         *
         * @param int $id
         * @param array $data
         * @return Insurance
         *
         * @throws ModelNotFoundException
         */
        public function update(int $id, array $data): Insurance
        {
            $insurance = $this->find($id);
            $insurance->update($data);
            return $insurance;
        }
        /**
         * Delete an insurance by its ID.
         *
         * @param int $id
         * @return void
         *
         * @throws ModelNotFoundException
         */
        public function delete(int $id): void
        {
            $insurance = $this->find($id);
            $insurance->delete();
        }
        /**
         * Restore a soft-deleted insurance by its ID.
         *
         * @param int $id
         * @return Insurance
         *
         * @throws ModelNotFoundException
         */
        public function restore(int $id): Insurance
        {
            $insurance = Insurance::withTrashed()->findOrFail($id);
            $insurance->restore();
            return $insurance;
        }

        /**
         * Bulk delete insurances by their IDs.
         *
         * @param array $ids
         * @return int Number of records deleted
         */
        public function bulkDelete(array $ids): int
        {
            return Insurance::whereIn('id', $ids)->delete();
        }

}