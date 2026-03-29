<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectTypeRequest;
use App\Http\Requests\UpdateProjectTypeRequest;
use App\Services\ProjectTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\Request;

/**
 * Class ProjectTypeController
 */
class ProjectTypeController extends Controller
{
    /**
     * @var ProjectTypeService
     */
    protected ProjectTypeService $projectTypeService;

    /**
     * @param ProjectTypeService $projectTypeService
     */
    public function __construct(ProjectTypeService $projectTypeService)
    {
        $this->projectTypeService = $projectTypeService;
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $projectTypes = $this->projectTypeService->getAll($request);

            return response()->json($projectTypes, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $type = $this->projectTypeService->find($id);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du type de projet : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param StoreProjectTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectTypeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $type = $this->projectTypeService->create($validated);

            return response()->json($type, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du type de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateProjectTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProjectTypeRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $type = $this->projectTypeService->update($id, $validated);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du type de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->projectTypeService->delete($id);

            return response()->json([
                'message' => 'Le type de projet a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array $ids
     * @return JsonResponse
     */
    public function bulkDelete(array $ids): JsonResponse
    {
        try {
            $this->projectTypeService->bulkDelete($ids);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $type = $this->projectTypeService->restore($id);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du type de projet : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
}
