<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteGroupTypeRequest;
use App\Http\Requests\StoreGroupTypeRequest;
use App\Http\Requests\UpdateGroupTypeRequest;
use App\Services\GroupTypeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GroupTypeController extends Controller
{
    /**
     * @var GroupTypeService
     */
    protected GroupTypeService $groupTypeService;

    public function __construct(GroupTypeService $groupTypeService)
    {
        $this->groupTypeService = $groupTypeService;
    }

    public function index(Request $request)
    {
        try{
            $groupTypes = $this->groupTypeService->getAll($request);
            return response()->json([
                'group_types' => $groupTypes,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des types de groupes',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreGroupTypeRequest $request): JsonResponse
    {
        try{
            $groupType = $this->groupTypeService->create($request->validated());
            return response()->json([
                'message' => 'Type de groupe créé avec succès',
                'group_type' => $groupType,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du type de groupe',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try{
            $groupType = $this->groupTypeService->show($id);
            return response()->json([
                'message' => 'Type de groupe récupéré avec succès',
                'group_type' => $groupType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du type de groupe',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $id, UpdateGroupTypeRequest $request): JsonResponse
    {
        try{
            $groupType = $this->groupTypeService->update($id, $request->validated());
            return response()->json([
                'message' => 'Type de groupe mis à jour avec succès',
                'group_type' => $groupType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du type de groupe',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try{
            $this->groupTypeService->delete($id);
            return response()->json([
                'message' => 'Type de groupe supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de groupe',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(DeleteGroupTypeRequest $request): JsonResponse
    {
        try{
            $this->groupTypeService->bulkDelete($request->validated());
            return response()->json([
                'message' => 'Types de groupes supprimés avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression des types de groupes',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try{
            $groupType = $this->groupTypeService->restore($id);
            return response()->json([
                'message' => 'Type de groupe restauré avec succès',
                'group_type' => $groupType,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la restauration du type de groupe',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
