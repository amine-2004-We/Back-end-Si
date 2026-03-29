<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\External;
use App\Services\ExternalService;
use App\Http\Requests\StoreExternalRequest;
use App\Http\Requests\UpdateExternalRequest;
use App\Http\Resources\ExternalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Training;
use App\Models\TrainingGroup;

class ExternalController extends Controller
{
    public function __construct(protected ExternalService $externalService)
    {
        //$this->authorizeResource(External::class, 'external');
    }

    public function index(Request $request): JsonResponse
    {
        //$this->authorize('viewAny', External::class);
        $externals = $this->externalService->getPaginatedExternals($request->all());
        return ExternalResource::collection($externals)->response();
    }

    public function store(StoreExternalRequest $request): JsonResponse
    {
        //$this->authorize('create', External::class);
        $data = $request->validated();

        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $external = $this->externalService->createExternal($data);
        return response()->json(new ExternalResource($external), Response::HTTP_CREATED);
    }

    public function show(External $external): JsonResponse
    {
        //$this->authorize('view', $external);
        return response()->json(new ExternalResource($this->externalService->findExternalById($external->id)));
    }

    public function update(UpdateExternalRequest $request, External $external): JsonResponse
    {
        //$this->authorize('update', $external);
        $data = $request->validated();

        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $updatedExternal = $this->externalService->updateExternal($external, $data);
        return response()->json(new ExternalResource($updatedExternal));
    }

    public function destroy(External $external): JsonResponse
    {
        //$this->authorize('delete', $external);
        $this->externalService->deleteExternal($external);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function toggleActivation(Request $request): JsonResponse
    {
        // $this->authorize('massUpdate', External::class);
        $validated = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $results = $this->externalService->toggleExternalActivation($validated['ids']);
        return response()->json($results);
    }

    public function getFormOptions(): JsonResponse
    {
        //$this->authorize('viewAny', External::class);
        $trainings = Training::select('id', 'title')->get();
        $trainingGroups = TrainingGroup::select('id', 'title')->get();

        return response()->json([
            'trainings' => $trainings,
            'training_groups' => $trainingGroups,
        ]);
    }
}
