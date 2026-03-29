<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use Illuminate\Http\Request;

use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class DepartmentController extends Controller
{
    /**
     * @var DepartmentService
     */
    private $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
        //$this->authorizeResource(Departement::class, 'departement');
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request):JsonResponse
    {
       try{
            $params = $request->all();

            $departments = $this->departmentService->getFilteredDepartments($params);

            return response()->json([
                'data' => DepartmentResource::collection($departments),
                'pagination' => [
                    'total' => $departments->total(),
                    'count' => $departments->count(),
                    'per_page' => $departments->perPage(),
                    'current_page' => $departments->currentPage(),
                    'total_pages' => $departments->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreDepartmentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreDepartmentRequest $request)
    {
       try{
        return response()->json([
            'message'=>"departement ajouté avec succès",
            'data'=> new DepartmentResource(
                $this->departmentService->create($request->validated()),
            )
        ], Response::HTTP_CREATED);
       }catch(\Exception $e){
            return response()->json([
                'error' => 'Erreur serveur : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
       }
    }

    /***
     * @param Departement $departement
     * @return JsonResponse
     */
    public function show(Departement $departement): JsonResponse
    {
        try{
            $department = $this->departmentService->getById($departement->id);

            if (!$department) {
                return response()->json(['message' => 'Department non trouvé.'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(new DepartmentResource($department));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateDepartmentRequest $request, Departement $departement): JsonResponse
    {
      try{
        // 1. Call the service to update the department.
        $department = $this->departmentService->update($departement->id, $request->validated());
        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => new DepartmentResource($department)
        ], Response::HTTP_OK);
      }
      catch(ModelNotFoundException $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
      }
      catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Departement $departement): JsonResponse
    {
        try {
            $this->authorize('delete', $departement);
            // 1. Call the service to delete the department.
            $this->departmentService->delete($departement->id);

            // 2. Return the response.
            return response()->json(['message' => 'Department deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Restore a soft-deleted department by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            // 1. Call the service to restore the department.
            $department = $this->departmentService->restore($id);

            // 2. Return the response.
            return response()->json([
                'message' => 'Department restored successfully.',
                'data' => new DepartmentResource($department)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Department not found: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Bulk delete departments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        try {
            // 1. Call the service to bulk delete departments.
            $deletedCount = $this->departmentService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount department(s) deleted successfully."]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting departments: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
