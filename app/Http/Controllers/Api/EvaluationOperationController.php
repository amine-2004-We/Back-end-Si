<?php

namespace App\Http\Controllers\Api;

use App\Enums\EvaluationStatusOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationOperationRequest;
use App\Http\Requests\UpdateEvaluationOperationRequest;
use App\Models\Collaborator;
use App\Models\EvaluationOperation;
use App\Models\TaskEvaluation;
use App\Services\EvaluationOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EvaluationOperationController extends Controller
{
    public function __construct(
        protected EvaluationOperationService $evaluationService
    ) {
        //$this->authorizeResource(EvaluationOperation::class, 'evaluationOperation');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getAllEvaluations($request->all());
            return response()->json($evaluations);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function list(): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getAllEvaluationsWithoutPagination();
            return response()->json($evaluations);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(EvaluationOperation $evaluationOperation): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationDetails($evaluationOperation->id);
            return response()->json($evaluation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreEvaluationOperationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                $validated['attachment'] = $path;
            }

            $evaluation = $this->evaluationService->createEvaluation(
                $validated,
                Auth::user(),
                Collaborator::findOrFail($validated['evaluator']),
                isset($validated['session_id']) ? TaskEvaluation::find($validated['session_id']) : null,
                $validated['criteria_scores'] ?? null
            );

            return response()->json($evaluation, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function update(UpdateEvaluationOperationRequest $request, EvaluationOperation $evaluationOperation): JsonResponse
    {
        try {

            //$this->authorize('update', $evaluationOperation);
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                $validated['attachment'] = $path;
            }

            $evaluation = $this->evaluationService->updateEvaluation(
                $evaluationOperation->id,
                $validated,
                isset($validated['evaluator_id']) ? Collaborator::find($validated['evaluator_id']) : null,
                isset($validated['session_id']) ? TaskEvaluation::find($validated['session_id']) : null,
                $validated['criteria_scores'] ?? null
            );

            return response()->json($evaluation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(EvaluationOperation $evaluationOperation): JsonResponse
    {
        try {
            $this->evaluationService->deleteEvaluation($evaluationOperation->id);
            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'ids' => 'required|array',
                'ids.*' => 'exists:evaluation_operations,id'
            ]);

            $this->evaluationService->bulkDeleteEvaluations($request->input('ids'));
            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->restoreEvaluation($id);
            return response()->json($evaluation);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error:' . $e->getMessage()
            ]);
        }
    }

    public function calculateScore(int $id): JsonResponse
    {
        try {
            $score = $this->evaluationService->calculateEvaluationScore($id);
            return response()->json(['total_score' => $score]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function getEvaluationWithCriteria(int $id): JsonResponse
    {
        try {
            $criteria = $this->evaluationService->getCriteriaWithEvaluationId($id);
            return response()->json(["criteria_scores" => $criteria]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    public function getSessions(): JsonResponse
    {
        try {
            $sessions=TaskEvaluation::all();
            return response()->json($sessions);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    public function getEvaluationsStatus(): JsonResponse
    {
        return response()->json(EvaluationStatusOperation::options());
    }
    

     public function getEvaluators()
{
    try {
        $authCollaborator = auth()->user()->collaborator;

        if (!$authCollaborator) {
            return response()->json([
                'error' => 'User is not associated with any collaborator'
            ], Response::HTTP_BAD_REQUEST);
        }

        $educators = Collaborator::query()
            ->where('hierarchical_superior', $authCollaborator->id)
           
            ->get();

        return response()->json([
            'data' => $educators
        ], Response::HTTP_OK);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
