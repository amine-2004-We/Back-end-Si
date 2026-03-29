<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBulkPresenceRequest;
use App\Http\Requests\StorePresenceRequest;
use App\Http\Requests\UpdatePresenceRequest;
use App\Http\Resources\PresenceResource;
use App\Models\Presence;
use App\Models\Task;
use App\Services\PresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *class PresenceController
 */
class PresenceController extends Controller
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
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $presences = $this->presenceService->getAll($request->all());
        return PresenceResource::collection($presences)->response();
    }

    /**
     * @param StorePresenceRequest $request
     * @return JsonResponse
     */
    public function store(StorePresenceRequest $request): JsonResponse
    {
        $presence = $this->presenceService->create($request->validated());
        return response()->json(new PresenceResource($presence->load(['personable', 'task', 'declarer', 'creator'])), Response::HTTP_CREATED);
    }

    /**
     * @param Presence $presence
     * @return JsonResponse
     */
    public function show(Presence $presence): JsonResponse
    {
        return response()->json(new PresenceResource($presence->load(['personable', 'task', 'declarer', 'creator'])));
    }

    /**
     * @param UpdatePresenceRequest $request
     * @param Presence $presence
     * @return JsonResponse
     */
    public function update(UpdatePresenceRequest $request, Presence $presence): JsonResponse
    {
        $this->presenceService->update($presence, $request->validated());
        return response()->json(new PresenceResource($presence->fresh()->load(['personable', 'task', 'declarer', 'creator'])));
    }

    /**
     * @param Presence $presence
     * @return JsonResponse
     */
    public function destroy(Presence $presence): JsonResponse
    {
        $this->presenceService->delete($presence);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @param Task $task
     * @return JsonResponse
     */
    public function getParticipantsForTask(Task $task): JsonResponse
    {
        $participants = $this->presenceService->getParticipantsForTask($task);
        return response()->json($participants);
    }


    /**
     * @param StoreBulkPresenceRequest $request
     * @return JsonResponse
     */
    public function storeBulk(StoreBulkPresenceRequest $request): JsonResponse
    {
        $this->presenceService->createOrUpdateBulk($request->validated());
        return response()->json(['message' => 'Presence records updated successfully.'], Response::HTTP_CREATED);
    }


    /**
     * @return JsonResponse
     */
    public function getFormOptions(): JsonResponse
    {
        return response()->json($this->presenceService->getFormOptions());
    }
}
