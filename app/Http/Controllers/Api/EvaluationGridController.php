<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationGridRequest;
use App\Http\Resources\EvaluationGridResource;
use App\Models\EvaluationGridModel;
use App\Services\EvaluationGridService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;

class EvaluationGridController extends Controller
{
    //
    /**
     * @var EvaluationGridService
     */
    protected EvaluationGridService $evaluationGridService;

    public function __construct( EvaluationGridService $evaluationGridService)
    {
        $this->evaluationGridService = $evaluationGridService;
        //$this->authorizeResource(EvaluationGridModel::class ,'evaluationGridModel');
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
            $evaluationGridService = $this->evaluationGridService->getAll($request);

            return response()->json([
                'data' => EvaluationGridResource::collection($evaluationGridService),
                'pagination' => [
                    'total' => $evaluationGridService->total(),
                    'count' => $evaluationGridService->count(),
                    'per_page' => $evaluationGridService->perPage(),
                    'current_page' => $evaluationGridService->currentPage(),
                    'total_pages' => $evaluationGridService->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function getEvaluationGrids(): JsonResponse
    {
        return response()->json($this->evaluationGridService->allTitles());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreEvaluationGridRequest $request
     * @return JsonResponse
     */
    public function store(StoreEvaluationGridRequest $request): JsonResponse
    {
        try {
            $validated= $request->validated();
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_evaluation_grid', $filename, 'public');
                $validated['attachment'] = $path;
            }
            $evaluationGrid = $this->evaluationGridService->create($validated);
            return response()->json([
                'message'=>"Grille d'évaluation ajouté avec succès.",
                'data' => new EvaluationGridResource($evaluationGrid)
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
    public function show(EvaluationGridModel $evaluationGridModel): JsonResponse
    {
        try {
            $evaluationGrid = $this->evaluationGridService->find($evaluationGridModel->id);

            if (!$evaluationGrid) {
                return response()->json(['message' => 'Grille d\'évaluation n\'pas trouvé'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(new EvaluationGridResource($evaluationGrid));
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param StoreEvaluationGridRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(StoreEvaluationGridRequest $request, EvaluationGridModel $evaluationGridModel): JsonResponse
    {
        try {
            $validated= $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '.' . $extension;
                $path = $file->storeAs('attachments_evaluation_grid', $filename, 'public');
                $validated['attachment'] = $path;

            }
            $evolutionGrid = $this->evaluationGridService->update($evaluationGridModel->id,$validated);

            return response()->json([
                'message' => "Grille d'évaluation modifiée avec succès.",
                'data' => new EvaluationGridResource($evolutionGrid)
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
    public function destroy(EvaluationGridModel $evaluationGridModel): JsonResponse
    {
        try {
            $deleted = $this->evaluationGridService->delete($evaluationGridModel->id);
            if (!$deleted) {
                return response()->json(['message' => "Grille d'évaluation introuvable"], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['message' => "Grille d'évaluation supprimer avec succès"], Response::HTTP_OK);
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
            $evolutionGrid= $this->evaluationGridService->restore($id);
            return response()->json([
                'message' => "Grille d'évaluation restauré avec succès",
                'data' => new EvaluationGridResource($evolutionGrid)
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
            $deletedCount = $this->evaluationGridService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount Grille d'évaluation supprimer (s) avec succès"]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans la suppression' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
