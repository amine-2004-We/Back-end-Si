<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompagnieAssuranceRequest;
use App\Http\Requests\UpdateCompagnieAssuranceRequest;
use App\Http\Resources\CompagnieAssuranceResource;
use App\Models\CompagnieAssurance;
use App\Services\CompagnieAssuranceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompagnieAssuranceController extends Controller
{
    public function __construct(protected CompagnieAssuranceService $service)
    {
        $this->authorizeResource(CompagnieAssurance::class, 'compagnie_assurance');
    }

    public function index(Request $request): JsonResponse
    {
        $compagnies = $this->service->getPaginated($request->all());
        return CompagnieAssuranceResource::collection($compagnies)->response();
    }

    public function store(StoreCompagnieAssuranceRequest $request): JsonResponse
    {
        $compagnie = $this->service->create($request->validated());
        return (new CompagnieAssuranceResource($compagnie))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(CompagnieAssurance $compagnieAssurance): CompagnieAssuranceResource
    {
        return new CompagnieAssuranceResource($this->service->find($compagnieAssurance->id));
    }

    public function update(UpdateCompagnieAssuranceRequest $request, CompagnieAssurance $compagnieAssurance): CompagnieAssuranceResource
    {
        $compagnie = $this->service->update($compagnieAssurance, $request->validated());
        return new CompagnieAssuranceResource($compagnie);
    }

    public function toggleActivation(Request $request): JsonResponse
    {
        $this->authorize('massUpdate', CompagnieAssurance::class);
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:compagnie_assurances,id',
        ]);
        $results = $this->service->toggleActivation($validated['ids']);
        return response()->json($results);
    }
}
