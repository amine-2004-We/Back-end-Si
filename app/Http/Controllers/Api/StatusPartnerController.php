<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStatusPartnerRequest;
use App\Http\Requests\UpdateStatusPartnerRequest;
use App\Http\Resources\StatusPartnerResource;
use App\Models\StatusPartner;
use App\Services\StatusPartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class StatusPartnerController
 * @package App\Http\Controllers\Api
 */
class StatusPartnerController extends Controller
{
    protected StatusPartnerService $service;

    public function __construct(StatusPartnerService $service)
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
        return StatusPartnerResource::collection($this->service->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreStatusPartnerRequest $request
     * @return JsonResponse
     */
    public function store(StoreStatusPartnerRequest $request): JsonResponse
    {
        try {
            $status = $this->service->store($request->validated());
            return (new StatusPartnerResource($status))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param StatusPartner $status_partner
     * @return StatusPartnerResource
     */
    public function show(StatusPartner $status_partner): StatusPartnerResource
    {
        return new StatusPartnerResource($status_partner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStatusPartnerRequest $request
     * @param StatusPartner $status_partner
     * @return JsonResponse
     */
    public function update(UpdateStatusPartnerRequest $request, StatusPartner $status_partner): JsonResponse
    {
        try {
            $this->service->update($status_partner, $request->validated());
            return (new StatusPartnerResource($status_partner))->response();
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param StatusPartner $status_partner
     * @return JsonResponse
     */
    public function destroy(StatusPartner $status_partner): JsonResponse
    {
        try {
            if (!$this->service->delete($status_partner)) {
                return response()->json([
                    'message' => 'This status is used and cannot be deleted.'
                ], 409);
            }
            return response()->json(null, 204);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}