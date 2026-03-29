<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStructurePartnerRequest;
use App\Http\Requests\UpdateStructurePartnerRequest;
use App\Http\Resources\StructurePartnerResource;
use App\Models\StructurePartner;
use App\Services\StructurePartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class StructurePartnerController
 * @package App\Http\Controllers\Api
 */
class StructurePartnerController extends Controller
{
    protected StructurePartnerService $service;

    public function __construct(StructurePartnerService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return StructurePartnerResource::collection($this->service->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreStructurePartnerRequest $request
     * @return JsonResponse
     */
    public function store(StoreStructurePartnerRequest $request): JsonResponse
    {
        try {
            $structure = $this->service->store($request->validated());
            return (new StructurePartnerResource($structure))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param StructurePartner $structure_partner
     * @return StructurePartnerResource
     */
    public function show(StructurePartner $structure_partner): StructurePartnerResource
    {
        return new StructurePartnerResource($structure_partner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStructurePartnerRequest $request
     * @param StructurePartner $structure_partner
     * @return JsonResponse
     */
    public function update(UpdateStructurePartnerRequest $request, StructurePartner $structure_partner): JsonResponse
    {
        try {
            $this->service->update($structure_partner, $request->validated());
            return (new StructurePartnerResource($structure_partner))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param StructurePartner $structure_partner
     * @return JsonResponse
     */
    public function destroy(StructurePartner $structure_partner): JsonResponse
    {
        try {
            if (!$this->service->delete($structure_partner)) {
                return response()->json([
                    'message' => 'This structure is used and cannot be deleted.'
                ], 409);
            }
            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}