<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectStatusRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Services\ProjectStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\Request;

/**
 * Class ProjectStatusController
 */
class ProjectStatusController extends Controller
{
    /**
     * @var ProjectStatusService
     */
    protected ProjectStatusService $projectStatusService;

    /**
     * @param ProjectStatusService $projectStatusService
     */
    public function __construct(ProjectStatusService $projectStatusService)
    {
        $this->projectStatusService = $projectStatusService;
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $projectStatuses = $this->projectStatusService->getAll($request);

            return response()->json($projectStatuses, Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $status = $this->projectStatusService->find($id);

            return response()->json($status, Response::HTTP_OK);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Statut de projet introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param StoreProjectStatusRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectStatusRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $status = $this->projectStatusService->create($validated);

            return response()->json($status, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du statut de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param  UpdateProjectStatusRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateProjectStatusRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $status = $this->projectStatusService->update($id, $validated);

            return response()->json($status, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du statut de projet : ' . $e->getMessage()
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
            $this->projectStatusService->delete($id);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du statut de projet : ' . $e->getMessage()
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
            $this->projectStatusService->bulkDelete($ids);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du statut de projet : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $status = $this->projectStatusService->restore($id);

            return response()->json($status, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du statut de projet : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
}
