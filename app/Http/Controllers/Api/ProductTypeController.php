<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductTypeRequest;
use App\Http\Requests\UpdateProductTypeRequest;
use App\Http\Resources\ProductTypeResource;
use App\Models\ProductType;
use App\Services\ProductTypeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProductTypeController extends Controller
{
    public function __construct(protected ProductTypeService $service) {}

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Pass all request parameters to the service's filtered method
            if ($request->boolean('all')) {
                $productsType = ProductType::all();

                return response()->json([
                    'data' => ProductTypeResource::collection($productsType)
                ], Response::HTTP_OK);
            }

            $productTypes = $this->service->getFilteredProductTypes($request->all());

            return response()->json([
                'data' => ProductTypeResource::collection($productTypes),
                'pagination' => [
                    'total' => $productTypes->total(),
                    'count' => $productTypes->count(),
                    'per_page' => $productTypes->perPage(),
                    'current_page' => $productTypes->currentPage(),
                    'total_pages' => $productTypes->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des types de produit.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->service->create($request->validated());
            return response()->json(new ProductTypeResource($type), Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création du type de produit.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $productType = $this->service->getById($id);

            if (!$productType) {
                return response()->json(['message' => 'Type de produit non trouvé'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(new ProductTypeResource($productType));
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'affichage du type de produit.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductTypeRequest $request, int $id): JsonResponse
    {
        try {
            $productType = $this->service->update($id, $request->validated());

            return response()->json([
                'message' => 'Type de produit mis à jour avec succès.',
                'data' => new ProductTypeResource($productType)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Type de produit non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du type de produit.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Type de produit supprimé avec succès.'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Type de produit non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression du type de produit.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucun type de produit sélectionné.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->service->bulkDelete($ids);
            return response()->json(['message' => "$deleted type(s) de produit supprimé(s) avec succès."]);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted product type by its ID.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $productType = $this->service->restore($id);
            return response()->json([
                'message' => 'Type de produit restauré avec succès.',
                'data' => new ProductTypeResource($productType)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Type de produit non trouvé ou déjà actif.'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
