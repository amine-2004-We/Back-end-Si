<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * class CategoryController
 */
class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    protected CategoryService $categoryService;

    /**
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        //$this->authorizeResource(Category::class, 'category');
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function index(Request $request): Response|JsonResponse
    {
        try {
            $params = $request->all();

            if ($request->boolean('all')) {
                $categories = Category::all();
                return response()->json(['data' => CategoryResource::collection($categories)], Response::HTTP_OK);
            }

            $categories = $this->categoryService->getFilteredCategories($params);

            return response()->json([
                'data' => CategoryResource::collection($categories),
                'pagination' => [
                    'total' => $categories->total(),
                    'count' => $categories->count(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'total_pages' => $categories->lastPage(),
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreCategoryRequest $request
     * @return Response|JsonResponse
     */
    public function store(StoreCategoryRequest $request): Response|JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());
            return response()->json([
                'data' => new CategoryResource($category),
                'message' => "Catégorie ajoutée avec succès"
            ], Response::HTTP_CREATED);
        } catch (QueryException $ex) {
            if ($ex->errorInfo[1] == 1062) {
                return response()->json(['status' => 'error', 'message' => 'Le nom de catégorie existe déjà.'], 409);
            }
            throw $ex;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        try {
            return response()->json(new CategoryResource($category), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Catégorie non trouvée ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateCategoryRequest $request
     * @param Category $category
     * @return Response|JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category): Response|JsonResponse
    {
        try {
            $this->authorize('update', $category);

            $updatedCategory = $this->categoryService->update($category->id, $request->validated());

            return response()->json([
                'data' => new CategoryResource($updatedCategory),
                'message' => "Catégorie mise à jour avec succès"
            ], Response::HTTP_OK);

        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Error => ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Category $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($this->categoryService->delete($category->id)) {
                return response()->json(['message' => "Catégorie supprimée avec succès"], Response::HTTP_NO_CONTENT);
            }
            return response()->json(['info' => 'La suppression de la catégorie a échoué.'], Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['error' => 'Catégorie non trouvée' . $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (\Exception $exception) {
            return response()->json(['error' => 'Server error' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonRespons
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $this->authorize('bulkDelete', Category::class);
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['message' => 'Aucune catégorie sélectionnée pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->categoryService->bulkDelete($ids);
            return response()->json(['message' => "$deleted catégorie(s) supprimée(s) avec succès."]);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Category $category
     * @return JsonResponse
     */
    public function getProductsByCategory(Category $category): JsonResponse
    {
        try {
            return response()->json([
                'category_name' => $category->name,
                'category_id' => $category->category_id,
                'products' => $category->products,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Error => ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Restore a soft-deleted category by its ID.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restore(Category $category): JsonResponse
    {

        $category = Category::withTrashed()->findOrFail($category->id);
        $this->authorize('restore', $category);
        $category->restore();

        return response()->json([
            'message' => 'Catégorie restaurée avec succès',
            'data' => $category
        ], 200);
    }
}
