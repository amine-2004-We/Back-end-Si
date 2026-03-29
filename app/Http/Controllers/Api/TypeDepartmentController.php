<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TypeDepartmentService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TypeDepartmentResource;
use App\Http\Requests\StoreTypeDepartmentRequest;
use App\Http\Requests\UpdateTypeDepartmentRequest;
use Illuminate\Http\Response;
use Exception;

class TypeDepartmentController extends Controller
{
    //
    /**
     * @var TypeDepartmentService
     */
    protected TypeDepartmentService $typeDepartmentService;

    public function __construct(TypeDepartmentService $typeDepartmentService)
    {
        $this->typeDepartmentService = $typeDepartmentService;
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 1. Get all parameters from the request URL.
            $params = $request->all();

            // 2. Call the service to get filtered type departments.
            $typeDepartments = $this->typeDepartmentService->getFilteredTypeDepartments($params);

            return response()->json([
                'data' => TypeDepartmentResource::collection($typeDepartments),
                'pagination' => [
                    'total' => $typeDepartments->total(),
                    'count' => $typeDepartments->count(),
                    'per_page' => $typeDepartments->perPage(),
                    'current_page' => $typeDepartments->currentPage(),
                    'total_pages' => $typeDepartments->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreTypeDepartmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreTypeDepartmentRequest $request): JsonResponse
    {
        try {
            $typeDepartment = $this->typeDepartmentService->createTypeDepartment($request->validated());
            return response()->json([
                'message'=>"type departement ajouté avec succès",
                'data' => new TypeDepartmentResource($typeDepartment)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $typeDepartment = $this->typeDepartmentService->findTypeDepartment($id);

            if (!$typeDepartment) {
                return response()->json(['message' => 'Type Department not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(new TypeDepartmentResource($typeDepartment));
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTypeDepartmentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateTypeDepartmentRequest $request, int $id): JsonResponse
    {
        try {
            $typeDepartment = $this->typeDepartmentService->updateTypeDepartment($id, $request->validated());

            return response()->json([
                'message' => "Type Department updated successfully",
                'data' => new TypeDepartmentResource($typeDepartment)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->typeDepartmentService->deleteTypeDepartment($id);
            if (!$deleted) {
                return response()->json(['message' => 'Type Department not found'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['message' => 'Type Department deleted successfully'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Restore a soft-deleted type department by its ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $typeDepartment = $this->typeDepartmentService->restoreTypeDepartment($id);
            return response()->json([
                'message' => 'Type Department restored successfully.',
                'data' => new TypeDepartmentResource($typeDepartment)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }    
    }
    /**
     * Bulk delete type departments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        try {
            $deletedCount = $this->typeDepartmentService->bulkDeleteTypeDepartments($ids);
            return response()->json(['message' => "$deletedCount type department(s) deleted successfully."]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting type departments: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }   
    }

}
