<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\Collaborator;
use App\Models\Evaluation;
use App\Models\Partner;
use App\Models\Project;
use App\Services\EvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EvaluationController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService
    ) {
        //$this->authorizeResource(Evaluation::class, 'evaluation');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $evaluations = $this->evaluationService->getAllEvaluations($request->all());
            return response()->json($evaluations);
        }catch (Throwable $e) {
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
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Evaluation $evaluation): JsonResponse
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationDetails($evaluation->id);
            return response()->json($evaluation);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_evaluation', $filename, 'public');
                $validated['attachment'] = $path;
            }

            $evaluation = $this->evaluationService->createEvaluation(
                $validated,
                Auth::user(),
                Collaborator::findOrFail($validated['evaluator_id']),
                isset($validated['object_project']) ? Project::find($validated['object_project']) : null,
                isset($validated['object_partner']) ? Partner::find($validated['object_partner']) : null,
                $validated['criteria_scores'] ?? null
            );

            return response()->json($evaluation, 201);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }

    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_evaluation', $filename, 'public');
                $validated['attachment'] = $path;
            }

            $evaluation = $this->evaluationService->updateEvaluation(
                $evaluation->id,
                $validated,
                isset($validated['evaluator_id']) ? Collaborator::find($validated['evaluator_id']) : null,
                isset($validated['object_project']) ? Project::find($validated['object_project']) : null,
                isset($validated['object_partner']) ? Partner::find($validated['object_partner']) : null,
                $validated['criteria_scores'] ?? null
            );

            return response()->json($evaluation);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Evaluation $evaluation): JsonResponse
    {
        try {
            $this->evaluationService->deleteEvaluation($evaluation->id);
            return response()->json(null, 204);
        }catch (Throwable $e) {
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
                'ids.*' => 'exists:evaluations,id'
            ]);

            $this->evaluationService->bulkDeleteEvaluations($request->input('ids'));
            return response()->json(null, 204);
        }catch (Throwable $e) {
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
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    public function calculateScore(int $id): JsonResponse
    {
        try {
            $score = $this->evaluationService->calculateEvaluationScore($id);
            return response()->json(['total_score' => $score]);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    public function getEvaluationWithCriteria(int $id): JsonResponse
    {
        try {
            $criteria = $this->evaluationService->getCriteriaWithEvaluationId($id);
            return response()->json(["criteria_scores"=>$criteria]);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
}
