<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBulkPresenceRequest;
use App\Http\Resources\PresenceSheetResource;
use App\Models\PresenceSheet; // Make sure this is imported
use App\Services\PresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *class PresenceSheetController
 */
class PresenceSheetController extends Controller
{
    /**
     * @var PresenceService
     */
    protected PresenceService $presenceService;

    /**
     * @param PresenceService $presenceService
     */
    public function __construct(PresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
    }

    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PresenceSheet::class);

        $sheets = $this->presenceService->getAll($request->all());
        return PresenceSheetResource::collection($sheets)->response();
    }

    /**
     * Summary of store
     * @param \App\Http\Requests\StoreBulkPresenceRequest $request
     * @return JsonResponse
     */
    public function store(StoreBulkPresenceRequest $request): JsonResponse
    {
        $this->authorize('create', PresenceSheet::class);

        $sheet = $this->presenceService->createOrUpdateSheet($request->validated());
        return response()->json(new PresenceSheetResource($sheet), Response::HTTP_CREATED);
    }

    /**
     * Summary of show
     * @param \App\Models\PresenceSheet $presenceSheet
     * @return JsonResponse
     */
    public function show(PresenceSheet $presenceSheet): JsonResponse
    {
        $this->authorize('view', $presenceSheet);
        return response()->json(new PresenceSheetResource($presenceSheet->load(['task', 'declarer', 'creator'])));
    }

    /**
     * Summary of update
     * @param \App\Http\Requests\StoreBulkPresenceRequest $request
     * @param \App\Models\PresenceSheet $presenceSheet
     * @return JsonResponse
     */
    public function update(StoreBulkPresenceRequest $request, PresenceSheet $presenceSheet): JsonResponse
    {
        $this->authorize('update', $presenceSheet);
        $updatedSheet = $this->presenceService->createOrUpdateSheet($request->validated());
        return response()->json(new PresenceSheetResource($updatedSheet));
    }

    /**
     * Summary of destroy
     * @param \App\Models\PresenceSheet $presenceSheet
     * @return JsonResponse
     */
    public function destroy(PresenceSheet $presenceSheet): JsonResponse
    {
        $this->authorize('delete', $presenceSheet);

        $presenceSheet->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Summary of validateSheet
     * @param \App\Models\PresenceSheet $presenceSheet
     * @return JsonResponse
     */
    public function validateSheet(PresenceSheet $presenceSheet): JsonResponse
    {
        $this->authorize('validatePresence', $presenceSheet);

        $presenceSheet->update([
            'validated_at' => now(),
            'validated_by' => Auth::id(),
        ]);

        return response()->json(new PresenceSheetResource($presenceSheet));
    }

    /**
     * Summary of toggleActivation
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        $this->authorize('update', PresenceSheet::class);

        $request->validate(['ids' => 'required|array']);
        $results = $this->presenceService->togglePresenceSheetActivation($request->ids);
        return response()->json($results);
    }

    /**
     * Summary of findByTaskAndDate
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function findByTaskAndDate(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PresenceSheet::class);

        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'event_date' => 'required|date_format:Y-m-d',
        ]);

        $sheet = PresenceSheet::where('task_id', $request->task_id)
            ->where('event_date', $request->event_date)
            ->first();

        if ($sheet) {
            $this->authorize('view', $sheet);
            return response()->json(new PresenceSheetResource($sheet));
        }

        return response()->json(['message' => 'Not Found'], 404);
    }
}
