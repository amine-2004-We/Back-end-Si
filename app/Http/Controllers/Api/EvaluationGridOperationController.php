<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationGridOperationRequest;
use App\Models\EvaluationGridOperation;
use App\Models\Project;
use App\Models\Program;
use App\Models\TaskEvaluation;
use App\Services\EvaluationGridOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EvaluationGridOperationController extends Controller
{
    public function __construct(
        protected EvaluationGridOperationService $evaluationGridOperationService
    ) {
        //$this->authorizeResource(EvaluationGridOperation::class, 'evaluationGridOperation');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $gridOperations = $this->evaluationGridOperationService->getAllGridOperations($request->all());
            return response()->json($gridOperations);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all grid operations without pagination for dropdowns/lists.
     */
    public function list(): JsonResponse
    {
        try {
            $gridOperations = $this->evaluationGridOperationService->getAllGridOperationsWithoutPagination();
            return response()->json($gridOperations);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EvaluationGridOperation $evaluationGridOperation): JsonResponse
    {
        try {
            $gridOperation = $this->evaluationGridOperationService->getGridOperationDetails($evaluationGridOperation->id);
            return response()->json($gridOperation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEvaluationGridOperationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                $validated['attachment'] = $path;
            }

            $gridOperation = $this->evaluationGridOperationService->createGridOperation(
                $validated,
                isset($validated['task_evaluation_id']) ? TaskEvaluation::find($validated['task_evaluation_id']) : null,
                isset($validated['project_id']) ? Project::find($validated['project_id']) : null,
                isset($validated['program_id']) ? Program::find($validated['program_id']) : null,
                Auth::user()
            );

            return response()->json($gridOperation, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreEvaluationGridOperationRequest $request, EvaluationGridOperation $evaluationGridOperation): JsonResponse
    {
        try {
            //$this->authorize('update', $evaluationGridOperation);
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                $validated['attachment'] = $path;
            }

            $gridOperation = $this->evaluationGridOperationService->updateGridOperation(
                $evaluationGridOperation->id,
                $validated,
                isset($validated['task_evaluation_id']) ? TaskEvaluation::find($validated['task_evaluation_id']) : null,
                isset($validated['project_id']) ? Project::find($validated['project_id']) : null,
                isset($validated['program_id']) ? Program::find($validated['program_id']) : null,
                isset($validated['user_id']) ? Auth::user() : null
            );

            return response()->json($gridOperation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EvaluationGridOperation $evaluationGridOperation): JsonResponse
    {
        try {
            //$this->authorize('delete', $evaluationGridOperation);
            $this->evaluationGridOperationService->deleteGridOperation($evaluationGridOperation->id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete multiple grid operations.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:table_evaluation_grid_operations,id'
            ]);

            $this->evaluationGridOperationService->bulkDeleteGridOperations($request->input('ids'));
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted grid operation.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $gridOperation = $this->evaluationGridOperationService->restoreGridOperation($id);
            return response()->json($gridOperation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getTaskEvaluations(): JsonResponse
    {
        return response()->json(TaskEvaluation::all());
    }
}
