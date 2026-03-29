<?php

namespace App\Http\Controllers\Api;

use App\Enums\EvaluationHrValueEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationHrRequest;
use App\Http\Resources\EvaluationHrResources;
use App\Models\EvaluationHr;
use App\Services\EvaluationHrService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EvaluationHrController extends Controller
{
    protected EvaluationHrService $evaluationHrService;

    public function __construct(EvaluationHrService $evaluationHrService)
    {
        $this->evaluationHrService = $evaluationHrService;
        $this->authorizeResource(EvaluationHr::class, 'evaluationHr');
    }

    /**
     * List evaluations with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $evaluations = $this->evaluationHrService->getAll($request);
            return response()->json($evaluations, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all evaluations (selected fields only).
     */
    public function getEvaluations(): JsonResponse
    {
        try {
            $evaluations = $this->evaluationHrService->allTypes();
            return response()->json($evaluations, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show a single evaluation by ID.
     */
    public function show(EvaluationHr $evaluationHr): JsonResponse
    {
        try {
            $evaluation = $this->evaluationHrService->find($evaluationHr->id);
            return response()->json(new EvaluationHrResources($evaluation), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de l\'évaluation : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a new evaluation.
     */
    public function store(StoreEvaluationHrRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $evaluation = $this->evaluationHrService->create($validated);
            return response()->json(new EvaluationHrResources($evaluation), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'évaluation : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an evaluation.
     */
    public function update(StoreEvaluationHrRequest $request, EvaluationHr $evaluationHr): JsonResponse
    {
        try {
            $validated = $request->validated();
            $evaluation = $this->evaluationHrService->update($evaluationHr->id, $validated);
            return response()->json(new EvaluationHrResources($evaluation), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'évaluation : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete an evaluation.
     */
    public function destroy(EvaluationHr $evaluationHr): JsonResponse
    {
        try {
            $this->evaluationHrService->delete($evaluationHr->id);
            return response()->json([
                'message' => 'L\'évaluation a été supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'évaluation : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete multiple evaluations.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:evaluations_hr,id',
            ]);

            $this->evaluationHrService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des évaluations : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted evaluation.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $evaluation = $this->evaluationHrService->restore($id);
            return response()->json(new EvaluationHrResources($evaluation), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de l\'évaluation : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    public function enums():JsonResponse
    {
        return response()->json(EvaluationHrValueEnum::options());
    }
}
