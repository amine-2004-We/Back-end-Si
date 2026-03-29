<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Http\Requests\StoreCycleRequest;
use App\Http\Requests\UpdateCycleRequest;
use App\Http\Requests\BulkDeleteCycleRequest;
use App\Http\Resources\CycleResource;
use App\Services\CycleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 *class CycleController
 */
class CycleController extends Controller
{
    /**
     * @var CycleService
     */
    protected CycleService $cycleService;

    /**
     * @param CycleService $cycleService
     */
    public function __construct(CycleService $cycleService)
    {
        $this->cycleService = $cycleService;
        //$this->authorizeResource(Cycle::class, 'cycle');
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'name',
            'created_by',
            'activation_status',
            'sort_by',
            'sort_direction',
            'per_page',
        ]);

        $perPage = $filters['per_page'] ?? 10;
        $cycles  = $this->cycleService->getPaginatedCycles($filters, $perPage);

        return response()->json([
            'data'  => CycleResource::collection($cycles->items()),
            'links' => [
                'first' => $cycles->url(1),
                'last'  => $cycles->url($cycles->lastPage()),
                'prev'  => $cycles->previousPageUrl(),
                'next'  => $cycles->nextPageUrl(),
            ],
            'meta'  => [
                'current_page' => $cycles->currentPage(),
                'from'         => $cycles->firstItem(),
                'last_page'    => $cycles->lastPage(),
                'links'        => $cycles->linkCollection()->toArray(),
                'path'         => $cycles->path(),
                'per_page'     => $cycles->perPage(),
                'to'           => $cycles->lastItem(),
                'total'        => $cycles->total(),
            ],
        ]);
    }

    /**
     * @param StoreCycleRequest $request
     * @return JsonResponse
     */
    public function store(StoreCycleRequest $request): JsonResponse
    {
        $cycle = $this->cycleService->createCycle($request->validated());

        if (!$cycle) {
            return response()->json(['message' => 'Failed to create cycle.'], 500);
        }

        return response()->json(new CycleResource($cycle->load('creator')), 201);
    }

    /**
     * @param Cycle $cycle
     * @return JsonResponse
     */
    public function show(Cycle $cycle): JsonResponse
    {
        $found = $this->cycleService->getCycle($cycle->id);
        if (!$found) {
            return response()->json(['message' => 'Cycle not found.'], 404);
        }

        return response()->json(new CycleResource($found));
    }

    /**
     * @param UpdateCycleRequest $request
     * @param Cycle $cycle
     * @return JsonResponse
     */
    public function update(UpdateCycleRequest $request, Cycle $cycle): JsonResponse
    {
        $updated = $this->cycleService->updateCycle($cycle->id, $request->validated());
        if (!$updated) {
            return response()->json(['message' => 'Failed to update cycle or cycle not found.'], 404);
        }
        return response()->json(new CycleResource($updated->load('creator')));
    }

    /**
     * @param Cycle $cycle
     * @return Response
     */
    public function destroy(Cycle $cycle): Response
    {
        if (!$this->cycleService->deleteCycle($cycle->id)) {
            return response('Failed to delete cycle.', 500);
        }
        return response()->noContent();
    }

    /**
     * @param BulkDeleteCycleRequest $request
     * @return Response
     * @throws AuthorizationException
     */
    public function bulkDelete(BulkDeleteCycleRequest $request): Response
    {
        //$this->authorize('massUpdate', Cycle::class);

        $ids = $request->input('cycle_ids');
        if (!$this->cycleService->bulkDeleteCycles($ids)) {
            return response('Failed to bulk delete cycles.', 500);
        }
        return response()->noContent();
    }
}
