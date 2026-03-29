<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingSessionRequest;
use App\Http\Requests\UpdateTrainingSessionRequest;
use App\Http\Resources\TrainingSessionResource;
use App\Models\TrainingSession;
use App\Services\TrainingSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class TrainingSessionController extends Controller
{
    /**
     * @param TrainingSessionService $service
     */
    public function __construct(protected TrainingSessionService $service)
    {
        $this->authorizeResource(TrainingSession::class, 'training_session');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $sessions = $this->service->getPaginated($request->all());
        return TrainingSessionResource::collection($sessions)->response();
    }

    /**
     * @param StoreTrainingSessionRequest $request
     * @return JsonResponse
     */
    public function store(StoreTrainingSessionRequest $request): JsonResponse
    {
        $session = $this->service->create($request->validated());
        return (new TrainingSessionResource($session))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param TrainingSession $trainingSession
     * @return TrainingSessionResource
     */
    public function show(TrainingSession $trainingSession): TrainingSessionResource
    {
        return new TrainingSessionResource($this->service->find($trainingSession->id));
    }

    /**
     * @param UpdateTrainingSessionRequest $request
     * @param TrainingSession $trainingSession
     * @return TrainingSessionResource
     */
    public function update(UpdateTrainingSessionRequest $request, TrainingSession $trainingSession): TrainingSessionResource
    {
        $session = $this->service->update($trainingSession, $request->validated());
        return new TrainingSessionResource($session);
    }


    /**
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getFormOptions(): JsonResponse
    {
        $this->authorize('create', TrainingSession::class);
        return response()->json($this->service->getFormOptions());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', TrainingSession::class);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:training_sessions,id',
        ]);

        $results = $this->service->toggleActivation($validated['ids']);
        return response()->json($results);
    }
}
