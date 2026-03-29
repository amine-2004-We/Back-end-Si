<?php

namespace App\Http\Controllers\Api;

use App\Models\RecruitmentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Resources\RecruitmentRequestResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecruitmentRequest;
use App\Http\Requests\UpdateRecruitmentRequest;
use App\Services\RecruitmentRequestService;
use Illuminate\Auth\Recaller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use Illuminate\Http\Response;

class RecruitmentRequestController extends Controller
{
    //
    public function __construct(protected RecruitmentRequestService $recruitmentRequestService)
    {
        $this->recruitmentRequestService = $recruitmentRequestService;
        //apply policies
        // $this->authorizeResource(RecruitmentRequest::class, 'recruitment_request');
    }
    public function index(Request $request)
    {
        $filters = $request->all();
        try {
            $recruitmentRequests = $this->recruitmentRequestService->getFilteredRecruitmentRequests($filters);
            return response()->json([
                'data' => RecruitmentRequestResource::collection($recruitmentRequests),
                'pagination' => [
                    'total' => $recruitmentRequests->total(),
                    'count' => $recruitmentRequests->count(),
                    'per_page' => $recruitmentRequests->perPage(),
                    'current_page' => $recruitmentRequests->currentPage(),
                ]
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Recruitment Requests not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'An error occurred while fetching recruitment requests'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function store(StoreRecruitmentRequest $request)
    {
        try {
            $data = $request->validated();
            $file = $request->hasFile('job_file') ? $request->file('job_file') : null;
            $recruitmentRequest = $this->recruitmentRequestService->createRecruitmentRequest($data, $file);
            return response()->json(new RecruitmentRequestResource($recruitmentRequest), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error creating recruitment request: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'An error occurred while creating the recruitment request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
       

    }
    public function show(RecruitmentRequest $recruitmentRequest)
    {
        try {
            return response()->json(new RecruitmentRequestResource($recruitmentRequest), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching the recruitment request'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(UpdateRecruitmentRequest $request, RecruitmentRequest $recruitmentRequest)
    {
        try {
            $data = $request->validated();
            $file = $request->hasFile('job_file') ? $request->file('job_file') : null;
            $updatedRecruitmentRequest = $this->recruitmentRequestService->updateRecruitmentRequest($recruitmentRequest->id, $data, $file);
            return response()->json(new RecruitmentRequestResource($updatedRecruitmentRequest), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Recruitment Request not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the recruitment request'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(RecruitmentRequest $recruitmentRequest)
    {
        try {
            $this->recruitmentRequestService->deleteRecruitmentRequest($recruitmentRequest->id);
            return response()->json(['message' => 'Recruitment Request deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Recruitment Request not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the recruitment request'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(RecruitmentRequest $recruitmentRequest)
    {
        try {
            $this->recruitmentRequestService->restoreRecruitmentRequest($recruitmentRequest->id);
            return response()->json(['message' => 'Recruitment Request restored successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Recruitment Request not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while restoring the recruitment request'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        try {
            $this->recruitmentRequestService->bulkDeleteRecruitmentRequests($ids);
            return response()->json(['message' => 'Recruitment Requests deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'One or more Recruitment Requests not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the recruitment requests'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function validateRequest(RecruitmentRequest $recruitmentRequest)
    {
        try {
            $recruitmentRequest = $this->recruitmentRequestService->validateRecruitmentRequest($recruitmentRequest->id);
            return response()->json(new \App\Http\Resources\RecruitmentRequestResource($recruitmentRequest), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function rejectRequest(RecruitmentRequest $recruitmentRequest)
    {
        try {
            $recruitmentRequest = $this->recruitmentRequestService->rejectRecruitmentRequest($recruitmentRequest->id);
            return response()->json(new \App\Http\Resources\RecruitmentRequestResource($recruitmentRequest), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

   


}
