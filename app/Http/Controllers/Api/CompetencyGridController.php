<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompetencyGridRequest;
use App\Http\Requests\UpdateCompetencyGridRequest;
use App\Models\CompetencyGrid;
use App\Models\CompetencyCriterion;
use App\Services\CompetencyGridService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompetencyGridController extends Controller
{
    /**
     * @var CompetencyGridService
     */
    public CompetencyGridService $competencyGridService;

    /**
     * @param CompetencyGridService $competencyGridService
     */
    public function __construct(CompetencyGridService $competencyGridService)
    {
        $this->competencyGridService = $competencyGridService;
    }

    /**
     * List competency grids.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $grids = $this->competencyGridService->getAll($request);

            if (method_exists($grids, 'getCollection') && $request->boolean('with_relations')) {
                $grids->getCollection()->load([
                    'criteria', 'creator'
                ]);
            }

            return response()->json($grids, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new competency grid.
     */
    public function store(StoreCompetencyGridRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $grid = $this->competencyGridService
                ->create($data)
                ->load(['criteria', 'creator']);

            return response()->json([
                'message' => 'Grille de compétences créée avec succès',
                'competency_grid' => $grid,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show a single competency grid.
     */
    public function show(CompetencyGrid $competencyGrid): JsonResponse
    {
        try {
            //$this->authorize('view', $competencyGrid);

            $competencyGrid->load(['criteria', 'creator']);

            return response()->json([
                'message' => 'Grille de compétences récupérée avec succès',
                'competency_grid' => $competencyGrid,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la grille: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a competency grid.
     */
    public function update(UpdateCompetencyGridRequest $request, CompetencyGrid $competencyGrid): JsonResponse
    {
        try {
            $data = $request->validated();

            $updatedGrid = $this->competencyGridService->update($data, $competencyGrid)
                ->load(['criteria', 'creator']);

            return response()->json([
                'message' => 'Grille de compétences mise à jour avec succès',
                'competency_grid' => $updatedGrid,
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la grille: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a competency grid.
     */
    public function destroy(CompetencyGrid $competencyGrid): JsonResponse
    {
        try {
            $this->competencyGridService->delete($competencyGrid->id);

            return response()->json([
                'message' => 'Grille de compétences supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la grille: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete competency grids.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:competency_grids,id'
            ])['ids'];

            $grids = CompetencyGrid::whereIn('id', $ids)->get();
            $count = $this->competencyGridService->bulkDestroy($grids);

            return response()->json([
                'message' => $count . ' grille(s) de compétences supprimée(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression des grilles: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted competency grid.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $grid = $this->competencyGridService->restore((int) $id);

            return response()->json([
                'message' => 'Grille de compétences restaurée avec succès',
                'competency_grid' => $grid,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la restauration de la grille: ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Options endpoint (for selects).
     */
    public function options(): JsonResponse
    {
        try {
            $formOptions = $this->competencyGridService->getFormOptions();

            return response()->json([
                'grid_types' => $formOptions['grid_types'],
                'grading_schemes' => $formOptions['grading_schemes'],
                'lifecycle_statuses' => $formOptions['lifecycle_statuses'],
                'competency_criteria' => CompetencyCriterion::select('id', 'identifier','title')->get(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
