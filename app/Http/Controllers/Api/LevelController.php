<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Http\Requests\StoreLevelRequest;
use App\Http\Requests\UpdateLevelRequest;
use App\Http\Requests\BulkToggleLevelStatusRequest;
use App\Http\Resources\LevelResource;
use App\Services\LevelService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 *class LevelController
 */
class LevelController extends Controller
{
    /**
     * @var LevelService
     */
    protected LevelService $levelService;

    /**
     * @param LevelService $levelService
     */
    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
        //$this->authorizeResource(Level::class, 'level');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('name');
        if ($searchTerm !== null) {
            $filters['name'] = $searchTerm;
        }

        $activationStatus = $request->input('activation_status');
        if ($activationStatus !== null) {
            $filters['status'] = $activationStatus;
        }

        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction');
        if ($sortBy !== null) {
            $filters['sort_by'] = $sortBy;
            $filters['sort_direction'] = $sortDirection ?? 'asc';
        }

        $levels = $this->levelService->getPaginatedLevels($filters, $perPage, $page);

        return response()->json([
            'data' => LevelResource::collection($levels->items()),
            'links' => [
                'first' => $levels->url(1),
                'last' => $levels->url($levels->lastPage()),
                'prev' => $levels->previousPageUrl(),
                'next' => $levels->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $levels->currentPage(),
                'from' => $levels->firstItem(),
                'last_page' => $levels->lastPage(),
                'links' => $levels->linkCollection()->toArray(),
                'path' => $levels->path(),
                'per_page' => $levels->perPage(),
                'to' => $levels->lastItem(),
                'total' => $levels->total(),
            ],
        ]);
    }

    /**
     * @param StoreLevelRequest $request
     * @return JsonResponse
     */
    public function store(StoreLevelRequest $request): JsonResponse
    {
        $level = $this->levelService->createLevel($request->validated());
        if (!$level) {
            return response()->json(['message' => 'Failed to create level.'], 500);
        }
        return response()->json(new LevelResource($level->load('cycle', 'creator')), 201);
    }

    /**
     * @param Level $level
     * @return JsonResponse
     */
    public function show(Level $level): JsonResponse
    {
        $foundLevel = $this->levelService->getLevel($level->id);
        if (!$foundLevel) {
            return response()->json(['message' => 'Level not found.'], 404);
        }
        return response()->json(new LevelResource($foundLevel));
    }

    /**
     * @param UpdateLevelRequest $request
     * @param Level $level
     * @return JsonResponse
     */
    public function update(UpdateLevelRequest $request, Level $level): JsonResponse
    {
        $updatedLevel = $this->levelService->updateLevel($level->id, $request->validated());
        if (!$updatedLevel) {
            return response()->json(['message' => 'Failed to update level or level not found.'], 404);
        }
        return response()->json(new LevelResource($updatedLevel->load('cycle', 'creator')));
    }

    /**
     * @param Level $level
     * @return Response
     */
    public function destroy(Level $level): Response
    {
        if (!$this->levelService->deleteLevel($level->id)) {
            return response(['message' => 'Failed to delete level.'], 500);
        }
        return response()->noContent();
    }

    /**
     * @param BulkToggleLevelStatusRequest $request
     * @return Response
     * @throws AuthorizationException
     */
    public function bulkToggleStatus(BulkToggleLevelStatusRequest $request): Response
    {
        //$this->authorize('massUpdate', Level::class);

        $ids = $request->input('level_ids');
        if (!$this->levelService->bulkToggleLevelsStatus($ids)) {
            return response(['message' => 'Failed to toggle status for levels.'], 500);
        }
        return response()->noContent();
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getFormOptions(): JsonResponse
    {
        //$this->authorize('viewAny', Level::class);

        $options = $this->levelService->getFormOptions();
        return response()->json($options);
    }
}
