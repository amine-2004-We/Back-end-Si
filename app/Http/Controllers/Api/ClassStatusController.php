<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassStatusRequest;
use App\Services\ClassStatusService;
use Illuminate\Http\JsonResponse;

class ClassStatusController extends Controller
{
    protected ClassStatusService $classStatusService;

    public function __construct(ClassStatusService $classStatusService)
    {
        $this->classStatusService = $classStatusService;
    }

    public function index(): JsonResponse
    {
        $data = $this->classStatusService->all();
        return response()->json($data);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->classStatusService->show($id);
        return response()->json($data);
    }

    public function store(StoreClassStatusRequest $request): JsonResponse
    {
        $created = $this->classStatusService->create($request);
        return response()->json($created, 201);
    }

    public function update(string $id, StoreClassStatusRequest $request): JsonResponse
    {
        $updated = $this->classStatusService->update($id, $request);
        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->classStatusService->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->classStatusService->restore($id);
        return response()->json(['message' => 'Restored successfully']);
    }

    public function bulkDelete(): JsonResponse
    {
        $ids = request()->input('ids', []);
        $this->classStatusService->bulkDelete($ids);
        return response()->json(['message' => 'Bulk deleted successfully']);
    }
}
