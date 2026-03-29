<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestTypeRequest;
use App\Services\RequestTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\Request;

class RequestTypeController extends Controller
{
    protected RequestTypeService $requestTypeService;

    public function __construct(RequestTypeService $requestTypeService)
    {
        $this->requestTypeService = $requestTypeService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $requestTypes = $this->requestTypeService->getAll($request);

            return response()->json($requestTypes, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getRequestTypes(): JsonResponse
    {
        try {
            $requestTypes = $this->requestTypeService->allTypes();
            return response()->json($requestTypes, Response::HTTP_OK);
        }catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $type = $this->requestTypeService->find($id);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du type de requête : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreRequestTypeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $type = $this->requestTypeService->create($validated);

            return response()->json($type, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du type de requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreRequestTypeRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $type = $this->requestTypeService->update($id, $validated);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du type de requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->requestTypeService->delete($id);

            return response()->json([
                'message' => 'Le type de requête a été supprimé avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(array $ids): JsonResponse
    {
        try {
            $this->requestTypeService->bulkDelete($ids);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $type = $this->requestTypeService->restore($id);

            return response()->json($type, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du type de requête : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
}
