<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationCriteriaRequest;
use App\Http\Resources\EvaluationCriteriaResource;
use App\Models\EvaluationCriteriaModel;
use App\Services\EvaluationCriteriaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;

class EvaluationCriteriaController extends Controller

{
    /**
     * @var EvaluationCriteriaService
     */
    protected EvaluationCriteriaService $evaluationCriteriaService;

    public function __construct( EvaluationCriteriaService $evaluationCriteriaService)
    {
        $this->evaluationCriteriaService = $evaluationCriteriaService;
        //$this->authorizeResource(EvaluationCriteriaModel::class, 'evaluationCriteriaModel');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $evaluationGridService = $this->evaluationCriteriaService->getAll($request);

            return response()->json([
                'data' => EvaluationCriteriaResource::collection($evaluationGridService),
                'pagination' => [
                    'total' => $evaluationGridService->total(),
                    'count' => $evaluationGridService->count(),
                    'per_page' => $evaluationGridService->perPage(),
                    'current_page' => $evaluationGridService->currentPage(),
                    'total_pages' => $evaluationGridService->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: '. $e->getMessage()], 500);
        }
    }

    public function getEvaluationCriteria(): JsonResponse
    {
        return response()->json($this->evaluationCriteriaService->allTitles());
    }
    /**
     * @param \App\Http\Requests\StoreEvaluationCriteriaRequest $request
     * @return JsonResponse
     */
    public function store(StoreEvaluationCriteriaRequest $request): JsonResponse
    {
        try {
            $evaluationCriteria = $this->evaluationCriteriaService->create($request->validated());

            return response()->json([
                'message'=>"Critère d'évaluation ajouté avec succès.",
                'data' => new EvaluationCriteriaResource($evaluationCriteria)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(EvaluationCriteriaModel $evaluationCriteriaModel): JsonResponse
    {
        try {
            $evaluationGrid = $this->evaluationCriteriaService->find($evaluationCriteriaModel->id);
            return response()->json(new EvaluationCriteriaResource($evaluationGrid));
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param StoreEvaluationCriteriaRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(StoreEvaluationCriteriaRequest $request, EvaluationCriteriaModel $evaluationCriteriaModel): JsonResponse
    {
        try {
            $evolutionGrid = $this->evaluationCriteriaService->update($evaluationCriteriaModel->id, $request->validated());

            return response()->json([
                'message' => "Critère d'évaluation modifiée avec succès.",
                'data' => new EvaluationCriteriaResource($evolutionGrid)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(EvaluationCriteriaModel $evaluationCriteriaModel): JsonResponse
    {
        try {
            $deleted = $this->evaluationCriteriaService->delete($evaluationCriteriaModel->id);
            if (!$deleted) {
                return response()->json(['message' => "Critère d'évaluation introuvable"], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['message' => "Critère d'évaluation supprimer avec succès"], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Restore a soft-deleted type department by its ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $evolutionGrid= $this->evaluationCriteriaService->restore($id);
            return response()->json([
                'message' => "Critère d'évaluation restauré avec succès",
                'data' => new EvaluationCriteriaResource($evolutionGrid)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Bulk delete type departments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        try {
            $deletedCount = $this->evaluationCriteriaService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount Critère d'évaluation supprimer (s) avec succès"]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans la suppression' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
