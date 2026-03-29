<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNaturePartnerRequest;
use App\Http\Requests\UpdateNaturePartnerRequest;
use App\Http\Resources\NaturePartnerResource;
use App\Models\NaturePartner;
use App\Services\NaturePartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class NaturePartnerController
 * @package App\Http\Controllers\Api
 */
class NaturePartnerController extends Controller
{
    protected NaturePartnerService $service;

    public function __construct(NaturePartnerService $service)
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
        return NaturePartnerResource::collection($this->service->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNaturePartnerRequest $request
     * @return JsonResponse
     */
    public function store(StoreNaturePartnerRequest $request): JsonResponse
    {
        try {
            $nature = $this->service->store($request->validated());
            return (new NaturePartnerResource($nature))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param NaturePartner $nature_partner
     * @return NaturePartnerResource
     */
    public function show(NaturePartner $nature_partner): NaturePartnerResource
    {
        return new NaturePartnerResource($nature_partner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNaturePartnerRequest $request
     * @param NaturePartner $nature_partner
     * @return JsonResponse
     */
    public function update(UpdateNaturePartnerRequest $request, NaturePartner $nature_partner): JsonResponse
    {
        try {
            $this->service->update($nature_partner, $request->validated());
            return (new NaturePartnerResource($nature_partner))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NaturePartner $nature_partner
     * @return JsonResponse
     */
    public function destroy(NaturePartner $nature_partner): JsonResponse
    {
        try {
            if (!$this->service->delete($nature_partner)) {
                return response()->json([
                    'message' => 'This nature is used and cannot be deleted.'
                ], 409);
            }
            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}