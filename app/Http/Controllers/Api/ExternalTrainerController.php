<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExternalTrainerRequest;
use App\Http\Requests\UpdateExternalTrainerRequest;
use App\Http\Resources\ExternalTrainerResource;
use App\Models\Cabinet;
use App\Models\ExternalTrainer;
use App\Services\ExternalTrainerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExternalTrainerController extends Controller
{
    public function __construct(protected ExternalTrainerService $service)
    {
        //$this->authorizeResource(ExternalTrainer::class, 'external_trainer');
    }

    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $trainers = $this->service->getPaginated($request->all());
        return ExternalTrainerResource::collection($trainers)->response();
    }

    /**
     * Summary of store
     * @param \App\Http\Requests\StoreExternalTrainerRequest $request
     * @return JsonResponse
     */
    public function store(StoreExternalTrainerRequest $request): JsonResponse
    {
        $trainer = $this->service->create($request->validated());
        return (new ExternalTrainerResource($trainer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Summary of show
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return ExternalTrainerResource
     */
    public function show(ExternalTrainer $externalTrainer): ExternalTrainerResource
    {
        $trainer = $this->service->findById($externalTrainer->id);
        return new ExternalTrainerResource($trainer);
    }

    /**
     * Summary of update
     * @param \App\Http\Requests\UpdateExternalTrainerRequest $request
     * @param \App\Models\ExternalTrainer $externalTrainer
     * @return ExternalTrainerResource
     */
    public function update(UpdateExternalTrainerRequest $request, ExternalTrainer $externalTrainer): ExternalTrainerResource
    {
        $trainer = $this->service->update($externalTrainer, $request->validated());
        return new ExternalTrainerResource($trainer);
    }

    public function destroy(ExternalTrainer $externalTrainer): JsonResponse
    {
        $this->service->delete($externalTrainer);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Toggle the activation status for a list of external trainers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', ExternalTrainer::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:external_trainers,id',
        ]);

        $results = $this->service->toggleActivation($request->input('ids'));

        return response()->json($results);
    }

    /**
     * Get options for form inputs, like cabinets.
     *
     * @return JsonResponse
     */
    public function cabinetOptions(): JsonResponse
    {
        //$this->authorize('viewAny', ExternalTrainer::class);

        return response()->json([
            'cabinets' => Cabinet::select('id', 'name')->orderBy('name')->get(),
        ]);
    }
}

