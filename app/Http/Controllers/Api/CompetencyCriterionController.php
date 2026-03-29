<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompetencyCriterionRequest;
use App\Http\Requests\UpdateCompetencyCriterionRequest;
use App\Http\Resources\CompetencyCriterionResource;
use App\Models\CompetencyCriterion;
use App\Services\CompetencyCriterionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *class CompetencyCriterionController
 */
class CompetencyCriterionController extends Controller
{
    /**
     * @param CompetencyCriterionService $service
     */
    public function __construct(protected CompetencyCriterionService $service)
    {
        //$this->authorizeResource(CompetencyCriterion::class, 'competency_criterion');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $criteria = $this->service->getPaginated($request->all());
        return CompetencyCriterionResource::collection($criteria)->response();
    }

    /**
     * @param StoreCompetencyCriterionRequest $request
     * @return JsonResponse
     */
    public function store(StoreCompetencyCriterionRequest $request): JsonResponse
    {
        $criterion = $this->service->create($request->validated());
        return (new CompetencyCriterionResource($criterion))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param CompetencyCriterion $competencyCriterion
     * @return CompetencyCriterionResource
     */
    public function show(CompetencyCriterion $competencyCriterion): CompetencyCriterionResource
    {
        $criterion = $this->service->findById($competencyCriterion->id);
        return new CompetencyCriterionResource($criterion);
    }

    /**
     * @param UpdateCompetencyCriterionRequest $request
     * @param CompetencyCriterion $competencyCriterion
     * @return CompetencyCriterionResource
     */
    public function update(UpdateCompetencyCriterionRequest $request, CompetencyCriterion $competencyCriterion): CompetencyCriterionResource
    {
        $criterion = $this->service->update($competencyCriterion, $request->validated());
        return new CompetencyCriterionResource($criterion);
    }

    /**
     * @param CompetencyCriterion $competencyCriterion
     * @return JsonResponse
     */
    public function destroy(CompetencyCriterion $competencyCriterion): JsonResponse
    {
        $this->service->delete($competencyCriterion);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', CompetencyCriterion::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:competency_criteria,id',
        ]);

        $results = $this->service->toggleActivation($request->input('ids'));
        return response()->json($results);
    }
}
