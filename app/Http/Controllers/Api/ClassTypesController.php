<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassTypesRequest;
use App\Services\ClassTypesService;
use Illuminate\Http\JsonResponse;

class ClassTypesController extends Controller
{
    protected ClassTypesService $classTypesService;

    public function __construct(ClassTypesService $classTypesService)
    {
        $this->classTypesService = $classTypesService;
    }

    public function index(): JsonResponse
    {
        $data = $this->classTypesService->all();
        return response()->json($data);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->classTypesService->show($id);
        return response()->json($data);
    }

    public function store(StoreClassTypesRequest $request): JsonResponse
    {
        $created = $this->classTypesService->create($request);
        return response()->json($created, 201);
    }

    public function update(string $id, StoreClassTypesRequest $request): JsonResponse
    {
        $updated = $this->classTypesService->update($id, $request);
        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->classTypesService->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->classTypesService->restore($id);
        return response()->json(['message' => 'Restored successfully']);
    }

    public function bulkDelete(): JsonResponse
    {
        $ids = request()->input('ids', []);
        $this->classTypesService->bulkDelete($ids);
        return response()->json(['message' => 'Bulk deleted successfully']);
    }
}
