<?php 

namespace App\Repositories;
use App\Models\JobPosting;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class JobPostingRepository
{
   /**
    * Get a paginated list of job postings with all filters and sorting.
    */
   public function getPaginatedJobPostings(array $params): LengthAwarePaginator
   {
     
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = JobPosting::onlyTrashed();
        } else {
            $query = JobPosting::query();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
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
        }
         else{
            $query->orderBy('created_at', 'desc'); 
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 10;

        return $query->paginate($perPage);
   }
   /**
    * Create a new job posting.
    *
    * @param array $data
    * @return JobPosting
    */
   public function createJobPosting(array $data): JobPosting
   {
       return JobPosting::create($data);
   }
    /**
     * Find a job posting by its ID.
     *
     * @param int $id
     * @return JobPosting|null
     */
   public function findJobPostingById(int $id): ?JobPosting
   {
       return JobPosting::withTrashed()->find($id);
   }
   /**
    * Update a job posting by its ID.
    *
    * @param int $id
    * @param array $data
    * @return JobPosting|null
    */
   public function updateJobPosting(int $id, array $data): ?JobPosting
   {
       $jobPosting = JobPosting::withTrashed()->find($id);
       if ($jobPosting) {
           $jobPosting->update($data);
           return $jobPosting;
       }
       return null;
   }
   /**
    * Delete a job posting by its ID.
    *
    * @param int $id
    * @return bool
   * @throws ConflictHttpException
    */
   public function deleteJobPosting(int $id): bool
   {
       $jobPosting = JobPosting::find($id);
       if (!$jobPosting) {
           throw new ConflictHttpException('Job posting not found');
       }
       return $jobPosting->delete();

   }
    /**
     * Restore a soft-deleted job posting by its ID.
     *
     * @param int $id
     * @return bool
     */
   public function restoreJobPosting(int $id): JobPosting
   {
         $jobPosting = JobPosting::withTrashed()->find($id);
         if ($jobPosting && $jobPosting->trashed()) {
              $jobPosting->restore();
              return $jobPosting;
         }
         throw new \Exception("Job posting not found or not deleted.");
      
    }

  
   /**
    * Bulk delete job postings by their IDs.
    *
    * @param array $ids
    * @return int Number of records deleted
    */
   public function bulkDeleteJobPostings(array $ids): int
   {
       return JobPosting::withTrashed()->whereIn('id', $ids)->forceDelete();
   }
    


}