<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteCategoryTypeRequest;
use App\Http\Requests\StoreCategoryTypeRequest;
use App\Http\Requests\UpdateCategoryTypeRequest;
use App\Services\CategoryTypeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryTypeController extends Controller
{
    /**
     * @var CategoryTypeService
     */
    protected CategoryTypeService $categoryTypeService;

    public function __construct(CategoryTypeService $categoryTypeService)
    {
        $this->categoryTypeService = $categoryTypeService;
    }

    public function index(Request $request)
    {
        try{
            $categoryTypes = $this->categoryTypeService->getAll($request);
            return response()->json([
                'category_types' => $categoryTypes,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des types de rubriques',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreCategoryTypeRequest $request): JsonResponse
    {
        try{
            $categoryType = $this->categoryTypeService->create($request->validated());
            return response()->json([
                'category_type' => $categoryType,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du type de rubrique',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function show(int $id): JsonResponse
    {
        try{
            $categoryType = $this->categoryTypeService->show($id);
            return response()->json([
                'category_type' => $categoryType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du type de rubrique',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $id, UpdateCategoryTypeRequest $request): JsonResponse
    {
        try{
            $categoryType = $this->categoryTypeService->update($id, $request->validated());
            return response()->json([
                'category_type' => $categoryType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du type de rubrique',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try{
            $this->categoryTypeService->delete($id);
            return response()->json([
                'message' => 'Type de rubrique supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de rubrique',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(DeleteCategoryTypeRequest $request): JsonResponse
    {
        try{
            $this->categoryTypeService->bulkDelete($request->validated());
            return response()->json([
                'message' => 'Types de rubriques supprimés avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des types de rubriques',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try{
            $categoryType = $this->categoryTypeService->restore($id);
            return response()->json([
                'category_type' => $categoryType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du type de rubrique',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
