<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestModelRequest;
use App\Http\Resources\RequestModelResource;
use App\Models\RequestModel;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class RequestController extends Controller
{
    protected RequestService $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
        //$this->authorizeResource(RequestModel::class,'requestModel');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $requests = $this->requestService->getAll($request);
            return response()->json($requests, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Erreur survenue dans le serveur.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getRequests(): JsonResponse
    {
        try {
            $requestTypes = $this->requestService->allTypes();
            return response()->json($requestTypes, Response::HTTP_OK);
        }catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show(RequestModel $requestModel): JsonResponse
    {
        try {
            $requestItem = $this->requestService->find($requestModel->id);
            return response()->json(new RequestModelResource($requestItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la requête : ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreRequestModelRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $requestItem = $this->requestService->create($validated);
            return response()->json(new RequestModelResource($requestItem), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(StoreRequestModelRequest $request, RequestModel $requestModel): JsonResponse
    {
        try {
            $validated = $request->validated();
            $requestItem = $this->requestService->update($requestModel->id, $validated);
            return response()->json(new RequestModelResource($requestItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(RequestModel $requestModel): JsonResponse
    {
        try {
            $this->requestService->delete($requestModel->id);
            return response()->json([
                'message' => 'La requête a été supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la requête : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:request,id',
            ]);

            $this->requestService->bulkDelete($validated['ids']);

            return response()->json([
                'message' => 'Suppression effectuée avec succès.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des requêtes : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $requestItem = $this->requestService->restore($id);
            return response()->json(new RequestModelResource($requestItem), Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration de la requête : ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
}
