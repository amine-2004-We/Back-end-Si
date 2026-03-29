<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ArticleController extends Controller
{
    protected ArticleService $articleService;
    

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 1. Get all parameters from the request URL.
            $params = $request->all();

            if ($request->boolean('all')) {
                $articles = Article::all();

                return response()->json([
                    'data' => ArticleResource::collection($articles)
                ], Response::HTTP_OK);
            }

            $articles = $this->articleService->getFilteredArticles($params);

            return response()->json([
                'data' => ArticleResource::collection($articles),
                'pagination' => [
                    'total' => $articles->total(),
                    'count' => $articles->count(),
                    'per_page' => $articles->perPage(),
                    'current_page' => $articles->currentPage(),
                    'total_pages' => $articles->lastPage(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): Response|JsonResponse
    {
        try {
            $article = $this->articleService->findWithTrashed($id);

            if (!$article) {
                return response()->json(['message' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(new ArticleResource($article));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Article non trouvé : ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreArticleRequest $request
     * @return JsonResponse
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            $article = $this->articleService->create($request->validated());
            return response()->json(new ArticleResource($article), Response::HTTP_CREATED);
        }
        catch(QueryException $ex) {
                if ($ex->errorInfo[1] == 1062) {
                    // Duplicate entry
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Le nom de article existe déjà.'
                    ], 409); // 409 Conflict
                }
                throw $ex;
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateArticleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateArticleRequest $request, int $id): JsonResponse
    {
        try {
            $article = $this->articleService->update($id, $request->validated());
            return response()->json([
                'message' => 'Article mis à jour avec succès.',
                'data' => new ArticleResource($article)
            ], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Article non trouvé' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->articleService->delete($id);
            return response()->json(['message' => 'Article supprimé avec succès'], $deleted ? Response::HTTP_NO_CONTENT : Response::HTTP_BAD_REQUEST);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Erreur serveur : ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['message' => 'Aucun article sélectionné pour suppression.'], Response::HTTP_BAD_REQUEST);
            }
            $deleted = $this->articleService->bulkDelete($ids);

            return response()->json(['message' => "$deleted article(s) supprimé(s) avec succès."], Response::HTTP_OK);
        } catch (ConflictHttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        } catch (Exception $exception) {
            return response()->json(['error' => 'Erreur serveur : ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Restore a soft-deleted article by its ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $article = $this->articleService->restore($id);
            return response()->json([
                'message' => 'Article restauré avec succès.',
                'data' => new ArticleResource($article)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Article non trouvé : ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
}
    /**
     * Update the brand of an article.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateBrand(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'brand' => 'required|string|max:255'
            ]);

            $article = $this->articleService->findWithTrashed($id);

            if (!$article) {
                return response()->json(['message' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $article->update(['brand' => $validated['brand']]);

            return response()->json([
                'message' => 'Marque de l\'article mise à jour avec succès.',
                'data' => new ArticleResource($article)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Article non trouvé : ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all unit options for articles (for dropdowns etc).
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitOptions(): \Illuminate\Http\JsonResponse
    {
        try {
            $units = \App\Models\Unit::select('id', 'name')->get();
            return response()->json(['data' => $units], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

}
