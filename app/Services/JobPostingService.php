<?php

namespace App\Services;
use App\Models\JobPosting;
use App\Repositories\JobPostingRepository;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Pagination\LengthAwarePaginator;

class JobPostingService
{
    private JobPostingRepository $jobPostingRepository;

    public function __construct(JobPostingRepository $jobPostingRepository)
    {
        $this->jobPostingRepository = $jobPostingRepository;
    }
    /**
     * Get a paginated list of job postings with all filters and sorting.
     */
    public function getPaginatedJobPostings(array $params): LengthAwarePaginator
    {
        return $this->jobPostingRepository->getPaginatedJobPostings($params);
    }
    /**
     * Create a new job posting.
     *
     * @param array $data
     * @return JobPosting
     */
    public function createJobPosting(array $data): JobPosting
    {
        return $this->jobPostingRepository->createJobPosting($data);
    }
    /**
     * Get a single job posting by its ID.
     *
     * @param int $id
     * @return JobPosting|null
     */
    public function findJobPostingById(int $id): ?JobPosting
    {
        return $this->jobPostingRepository->findJobPostingById($id);
    }
    /**
     * Update a job posting by its ID.
     *
     * @param int $id
     * @param array $data
     * @return JobPosting
     */
    public function updateJobPosting(int $id, array $data): ?JobPosting
    {
        return $this->jobPostingRepository->updateJobPosting($id, $data);
    }
    /**
     * Delete a job posting by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteJobPosting(int $id): bool
    {
        return $this->jobPostingRepository->deleteJobPosting($id);
    }
    /**
     * Restore a soft-deleted job posting by its ID.
     *
     * @param int $id
     * @return JobPosting
     */
    public function restoreJobPosting(int $id): JobPosting|bool
    {
        return $this->jobPostingRepository->restoreJobPosting($id);
    }

    /**
     * Bulk delete job postings by their IDs.
     *
     * @param array $ids
     * @return int
     */    public function bulkDeleteJobPostings(array $ids): int
    {
        return $this->jobPostingRepository->bulkDeleteJobPostings($ids);
    }

}