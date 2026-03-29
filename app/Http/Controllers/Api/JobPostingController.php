<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use App\Services\JobPostingService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    private JobPostingService $jobPostingService;

    public function __construct(JobPostingService $jobPostingService)
    {
        $this->jobPostingService = $jobPostingService;
        //$this->authorizeResource(JobPosting::class, 'jobPosting');
    }
    /**
     * Display a paginated listing of the job postings with filtering and sorting.
     */
    public function index(Request $request): JsonResponse
    {
      try{
        $params = $request->all();
        $jobPostings = $this->jobPostingService->getPaginatedJobPostings($params);
        return response()->json([
            'data' => JobPostingResource::collection($jobPostings),
            'pagination' => [
                'total' => $jobPostings->total(),
                'count' => $jobPostings->count(),
                'per_page' => $jobPostings->perPage(),
                'current_page' => $jobPostings->currentPage(),
                'total_pages' => $jobPostings->lastPage(),
            ]
        ], Response::HTTP_OK);
      } catch (Exception $e) {
          return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }
    /**
     * Store a newly created job posting in storage.
     */
    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $jobPosting = $this->jobPostingService->createJobPosting($data);
            return response()->json(new JobPostingResource($jobPosting), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified job posting.
     */
    public function show(JobPosting $jobPosting): JsonResponse
    {
        try{
            $jobPosting = $this->jobPostingService->findJobPostingById($jobPosting->id);
            if (!$jobPosting) {
                throw new NotFoundHttpException('Job Posting not found');
            }
            return response()->json(new JobPostingResource($jobPosting), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    /**
     * Update the specified job posting in storage.
     */
    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): JsonResponse
    {
        try {
            $data = $request->validated();
            $jobPosting = $this->jobPostingService->updateJobPosting($jobPosting->id, $data);
            return response()->json(new JobPostingResource($jobPosting), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified job posting from storage.
     */
    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        try {
            $this->jobPostingService->deleteJobPosting($jobPosting->id);
            return response()->json(['message' => 'Job posting deleted successfully'], Response::HTTP_NO_CONTENT);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Job posting not found'], Response::HTTP_NOT_FOUND);
        }
         catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Restore a soft-deleted job posting.
     */
    public function restore(JobPosting $jobPosting): JsonResponse
    {
        try {
            $jobPosting = $this->jobPostingService->restoreJobPosting($jobPosting->id);
            return response()->json(new JobPostingResource($jobPosting), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Bulk delete job postings by their IDs.
     */    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['error' => 'Invalid IDs provided'], Response::HTTP_BAD_REQUEST);
            }
            $deletedCount = $this->jobPostingService->bulkDeleteJobPostings($ids);
            return response()->json(['deleted' => $deletedCount], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
