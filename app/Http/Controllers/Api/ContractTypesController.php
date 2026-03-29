<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractTypeRequest;
use App\Http\Resources\ContractTypeResource;
use App\Services\ContractTypesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ContractTypesController extends Controller
{
    protected ContractTypesService $contractTypesService;
    public function __construct(ContractTypesService $contractTypesService)
    {
        $this->contractTypesService = $contractTypesService;
    }
    /**
     * Get all contract types.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');
            $contractTypes = $withTrashed
                ? $this->contractTypesService->getAllWithTrashed()
                : $this->contractTypesService->all();

            return response()->json([
                'data' => ContractTypeResource::collection($contractTypes),
                'pagination' => [
                    'total' => $contractTypes->total(),
                    'count' => $contractTypes->count(),
                    'per_page' => $contractTypes->perPage(),
                    'current_page' => $contractTypes->currentPage(),
                    'total_pages' => $contractTypes->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(string $id): JsonResponse
    {
        try {
            $type = $this->contractTypesService->getContractTypebyId($id);
            return response()->json($type);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type de contrat non trouvé'], 404);
        }
    }
    /**
     * Store a new contract type.
     */
    public function store(StoreContractTypeRequest $request): JsonResponse
    {
        try {
            return response()->json(
                new ContractTypeResource($this->contractTypesService->create( $request->validated())),
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update a contract type.
     */
    public function update(StoreContractTypeRequest $request, string $id): JsonResponse
    {
        try {
            return response()->json($this->contractTypesService->update($request->validated(), $id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type de contrat non trouvé'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }
    /**
     * Delete a contract type.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->contractTypesService->delete($id);
            return response()->json(['message' => 'Type de contrat supprimé avec succès'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type de contrat non trouvé'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }
    /**
     * Bulk delete contract types.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucun type sélectionné pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->contractTypesService->bulkDelete($ids);
            return response()->json(['message' => "$deleted type(s) de contrat supprimé(s) avec succès."], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Erreur serveur : ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(int $id): JsonResponse
    {
        try {
            $contractType = $this->contractTypesService->restore($id);
            return response()->json([
                'message' => 'Class successfully restored.',
                'data' => new ContractTypeResource($contractType),
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
