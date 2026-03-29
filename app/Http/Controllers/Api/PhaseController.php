<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Services\PhaseService;
use App\Http\Requests\StorePhaseRequest;
use App\Http\Requests\UpdatePhaseRequest;
use App\Http\Resources\PhaseResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PhaseController extends Controller
{
    public function __construct(protected PhaseService $phaseService) {}

    /**
     * Summary of index
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        //$this->authorize('viewAny', Phase::class);
        $phases = $this->phaseService->getPaginatedPhases($request->all());
        return PhaseResource::collection($phases)->response();
    }

    public function phasesBytag(Request $request): JsonResponse
    {
        //$this->authorize('viewAny', Phase::class);
        $phases = $this->phaseService->getPaginatedPhasesByTag($request->all());
        return PhaseResource::collection($phases)->response();
    }

    /**
     * Summary of store
     * @param StorePhaseRequest $request
     * @return JsonResponse
     */
    public function store(StorePhaseRequest $request): JsonResponse
    {
        //$this->authorize('create', Phase::class);
        $phase = $this->phaseService->createPhase($request->validated());
        return response()->json(new PhaseResource($phase), Response::HTTP_CREATED);
    }

    /**
     * Summary of show
     * @param Phase $phase
     * @return JsonResponse
     */
    public function show(Phase $phase): JsonResponse
    {
        //$this->authorize('view', $phase);
        return response()->json(new PhaseResource($this->phaseService->findPhaseById($phase->id)));
    }

    /**
     * Summary of update
     * @param UpdatePhaseRequest $request
     * @param Phase $phase
     * @return JsonResponse
     */
    public function update(UpdatePhaseRequest $request, Phase $phase): JsonResponse
    {
        //$this->authorize('update', $phase);
        $updatedPhase = $this->phaseService->updatePhase($phase, $request->validated());
        return response()->json(new PhaseResource($updatedPhase));
    }

    /**
     * Summary of destroy
     * @param Phase $phase
     * @return JsonResponse
     */
    public function destroy(Phase $phase): JsonResponse
    {
        //$this->authorize('delete', $phase);
        $this->phaseService->deletePhase($phase);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Summary of toggleActivation
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Phase::class);
        $validated = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $results = $this->phaseService->togglePhaseActivation($validated['ids']);
        return response()->json($results);
    }

    /**
     * Summary of getFormOptions
     * @return JsonResponse
     */
    public function getFormOptions(): JsonResponse
    {
        $types = ['Préparation', 'Mise en œuvre', 'Suivi-évaluation', 'Clôture'];
        $statuses = ['Prévue', 'En cours', 'Terminée', 'Archivée'];

        $projects = Project::select('id', 'project_name as title')->get();
        $users = User::select('id', 'name')->get();

        return response()->json([
            'types' => $types,
            'statuses' => $statuses,
            'projects' => $projects,
            'users' => $users,
        ]);
    }
}
