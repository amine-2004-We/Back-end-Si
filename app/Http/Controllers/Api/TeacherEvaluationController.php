<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TeacherEvaluationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherEvaluationRequest;
use App\Services\TeacherEvaluationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\UpdateTeacherEvaluationRequest;
use Illuminate\Support\Facades\Storage;
class TeacherEvaluationController extends Controller
{
    private $service;

    public function __construct(TeacherEvaluationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $evaluations = $this->service->getFiltered($filters);

            return response()->json([
                'data' => TeacherEvaluationResource::collection($evaluations),
                'pagination' => [
                    'total' => $evaluations->total(),
                    'count' => $evaluations->count(),
                    'per_page' => $evaluations->perPage(),
                    'current_page' => $evaluations->currentPage(),
                ]
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluations not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluations not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching teacher evaluations: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching teacher evaluations'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreTeacherEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->service->create($request->all());
            return response()->json([
                'data' => new TeacherEvaluationResource($evaluation)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error creating teacher evaluation: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating teacher evaluation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $evaluation = $this->service->find($id);
            return response()->json([
                'data' => new TeacherEvaluationResource($evaluation)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluation not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluation not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error fetching teacher evaluation: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching teacher evaluation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $id, UpdateTeacherEvaluationRequest $request): JsonResponse
    {
        try {
            $evaluation = $this->service->update($id, $request->validated());
            return response()->json([
                'data' => new TeacherEvaluationResource($evaluation)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluation not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluation not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error updating teacher evaluation: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating teacher evaluation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Teacher Evaluation deleted successfully'], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluation not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluation not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error deleting teacher evaluation: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting teacher evaluation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->service->restore($id);
            return response()->json(['message' => 'Teacher Evaluation restored successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluation not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluation not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error restoring teacher evaluation: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while restoring teacher evaluation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        try {
            $deletedCount = $this->service->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount teacher evaluations deleted successfully"], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting teacher evaluations: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the teacher evaluations'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        try {
            $evaluation = $this->service->find($id);
            $evaluation->status = $request->input('status');
            $evaluation->save();

            return response()->json([
                'data' => new TeacherEvaluationResource($evaluation)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Evaluation not found: ' . $e->getMessage());
            return response()->json(['message' => 'Teacher Evaluation not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Error updating teacher evaluation status: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating teacher evaluation status'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
