<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    protected ProductService $productService;

    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        //$this->authorizeResource(Product::class, 'product');
    }

    /**
     * @return Response|JsonResponse
     */
    public function index(Request $request): Response|JsonResponse
    {
        try {
            // 1. Get all parameters from the request URL.
            $params = $request->all();

            if ($request->boolean('all')) {
                $products = Product::all();

                return response()->json([
                    'data' => ProductResource::collection($products)
                ], Response::HTTP_OK);
            }

            $products = $this->productService->getFilteredProducts($params);


            return response()->json([
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'total' => $products->total(),
                    'count' => $products->count(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * @param string $productId
     * @return JsonResponse
     */
    public function show(Product $product): Response|JsonResponse
    {
        try {
            // Vérifie la policy automatiquement grâce à authorizeResource si tu l'as mis dans le constructeur
            $this->authorize('view', $product);

            return response()->json(new ProductResource($product), Response::HTTP_OK);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Produit créé avec succès.',
                'data' => new ProductResource($this->productService->create($request->validated()))
            ], Response::HTTP_CREATED);
        }
        catch(QueryException $ex) {
                if ($ex->errorInfo[1] == 1062) {
                    // Duplicate entry
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Le nom de produit existe déjà.'
                    ], 409); // 409 Conflict
                }
                throw $ex;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création du produit.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $this->authorize('update', $product);

            $updatedProduct = $this->productService->update($product->id, $request->validated());

            return response()->json([
                'data' => new ProductResource($updatedProduct),
                'message' => 'Le produit a été mis à jour avec succès.'
            ], Response::HTTP_OK);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->authorize('delete', $product);

            $deleted = $this->productService->delete($product->id);

            if ($deleted) {
                return response()->json(['message' => 'Produit supprimé avec succès'], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Aucune suppression effectuée'], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Erreur lors de la suppression', 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $this->authorize('bulkDelete', Product::class);
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['message' => 'Aucune produit sélectionnée pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->productService->bulkDelete($ids);

            return response()->json(['message' => "$deleted produit(s) supprimée(s) avec succès."], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * @param int $productId
     * @return JsonResponse
     */
    public function restore(Product $product): JsonResponse
    {
        try {
            $product = $this->productService->restore($product->id);
            return response()->json([
                'message' => 'Produit restauré avec succès.',
                'data' => new ProductResource($product)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Produit non trouvé : ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
