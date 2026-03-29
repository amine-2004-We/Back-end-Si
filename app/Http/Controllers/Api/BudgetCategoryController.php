<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBudgetCategoryRequest;
use App\Http\Requests\StoreBudgetCategoryRequest;
use App\Http\Requests\UpdateBudgetCategoryRequest;
use App\Services\BudgetCategoryService;
use HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * class BudgetCategoryController
 */
class BudgetCategoryController extends Controller
{
    /**
     * @var BudgetCategoryService
     */
    public BudgetCategoryService $budgetCategoryService;

    /**
     * @param BudgetCategoryService $budgetCategoryService
     */
    public function __construct(BudgetCategoryService $budgetCategoryService)
    {
        $this->budgetCategoryService = $budgetCategoryService;
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try{
            $budgetCategories = $this->budgetCategoryService->getAll($request);
            return response()->json($budgetCategories, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur!' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreBudgetCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreBudgetCategoryRequest $request): JsonResponse
    {
        try{
            $data = $request->validated();
            $budgetCategory = $this->budgetCategoryService->create($data);

            return response()->json($budgetCategory, Response::HTTP_CREATED);
        }catch (Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try{
            $budgetCategory = $this->budgetCategoryService->show($id);
            return response()->json($budgetCategory, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateBudgetCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBudgetCategoryRequest $request, int $id): JsonResponse
    {
        try{
            $data = $request->validated();
            $budgetCategory = $this->budgetCategoryService->update($id, $data);
            return response()->json($budgetCategory, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id) : JsonResponse
    {
        try{
            $budgetCategory = $this->budgetCategoryService->delete($id);
            return response()->json($budgetCategory, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Erreur  dans le serveur! ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DestroyBudgetCategoryRequest $request
     * @return JsonResponse
     */
    public function  bulkDestroy(DestroyBudgetCategoryRequest $request)
    {
        try{
            $ids = $request->validated();
            $deleted = $this->budgetCategoryService->bulkDestroy($ids);
            $count = count($ids);
            return response()->json([
                'message' => $count.' rubrique(s) budgétaire supprimée avec succès !'
            ], Response::HTTP_NO_CONTENT);
        }catch(Exception $e){
            Log::error($e->getMessage());

            return response()->json(['error' => 'Erreur dans le serveur !'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $budgetCategory = $this->budgetCategoryService->restore($id);

            return response()->json([
                'message' => 'Rubrique budgétaire restaurée avec succès.',
                'data' => $budgetCategory
            ], Response::HTTP_OK);

        } catch (HttpException $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], $e->getStatusCode());

        } catch (Exception $e) {

            return response()->json([
                'error' => 'Erreur dans le serveur ! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
