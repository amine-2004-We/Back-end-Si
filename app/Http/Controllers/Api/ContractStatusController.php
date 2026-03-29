<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractStatusRequest;
use App\Http\Resources\ContractStatusResource;
use App\Services\ContractStatusService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContractStatusController
 * @package App\Http\Controllers\Api
 */
class ContractStatusController extends Controller
{
    protected ContractStatusService $contractStatusService;

    public function __construct(ContractStatusService $contractStatusService)
    {
        $this->contractStatusService = $contractStatusService;
    }
    /**
     * Get all contract statuses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $contractStatuses = $withTrashed
                ? $this->contractStatusService->getAllWithTrashed()
                : $this->contractStatusService->all();

            return response()->json([
                'data' => ContractStatusResource::collection($contractStatuses),
                'pagination' => [
                    'total' => $contractStatuses->total(),
                    'count' => $contractStatuses->count(),
                    'per_page' => $contractStatuses->perPage(),
                    'current_page' => $contractStatuses->currentPage(),
                    'total_pages' => $contractStatuses->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(string $id)
    {
        try {
            $status = $this->contractStatusService->findContractStatus($id);
            return response()->json($status);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Status not found'], 404);
        }
    }
    /**
     * Create a new contract status.
     */
    public function store(StoreContractStatusRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json(
                new ContractStatusResource($this->contractStatusService->create($request->validated()))
                , Response::HTTP_CREATED
            );
        }catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update an existing contract status.
     */
    public function update(StoreContractStatusRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        try{
            $validated = $request->validated();
            $collaborateur = $this->contractStatusService->update($id, $validated);
            return response()->json(
                new ContractStatusResource($collaborateur),
                Response::HTTP_OK
            );
        } catch (ValidationException $e){
            return response()->json([
                'error' => 'Erreur de validation',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Collaborateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }catch (NotFoundHttpException $e){
            return response()->json([
                'error' => 'Ressource introuvable'
            ], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json([
                'error' => 'Erreur HTTP : ' . $e->getMessage()
            ], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erreur serveur : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Delete a contract status.
     */
    public function destroy(string $id)
    {
        try {
            $this->contractStatusService->delete($id);
            return response()->json(['message' => 'Status deleted successfully'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Status not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Bulk delete contract statuses.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucun Statut sélectionné pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->contractStatusService->bulkDelete($ids);
            return response()->json(['message' => "$deleted statuts(s) de contrat supprimé(s) avec succès."], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Erreur serveur : ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $contractStatId):JsonResponse
    {
        try {
            $contractStatus=$this->contractStatusService->restore($contractStatId);
            return response()->json([
                'message' => 'Statut contract restaurer.',
                'restored' => $contractStatus
            ],Response::HTTP_OK);
        }catch (Exception $e){
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }
}
