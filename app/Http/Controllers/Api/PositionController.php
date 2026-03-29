<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PositionController extends Controller
{
    protected PositionService $service;

    public function __construct(PositionService $service)
    {
        $this->service = $service;
        //$this->authorizeResource(Position::class, 'position');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $withTrashed = $request->boolean('with_trashed');

            if ($withTrashed) {
                $positions = $this->service->getArchived();
            } else {
                $positions = $this->service->getAll();
            }
            return response()->json($positions);
        }catch (Throwable $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json($this->service->find($position->id));
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        $position = $this->service->create($request->validated());
        return response()->json($position, 201);
    }

    public function update(StorePositionRequest $request, Position $position): JsonResponse
    {
        $position = $this->service->update($position->id, $request->validated());
        return response()->json($position);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->service->delete($position->id);
        return response()->json(['message' => 'Position deleted']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->service->restore($id);
        return response()->json(['message' => 'Position restored']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $deletedCount = $this->service->bulkDelete($request->input('ids', []));
        return response()->json(['message' => "$deletedCount positions deleted"]);
    }

}
