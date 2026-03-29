<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeletePlaceRequest;
use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Models\Place;
use App\Services\PlaceService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class PlaceController
 */
class PlaceController extends Controller
{
    /**
     * @param PlaceService $placeService
     */
    public function __construct(protected PlaceService $placeService)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $places = $this->placeService->getAll($request);
            return response()->json($places, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Place $place
     * @return JsonResponse
     */
    public function show(Place $place): JsonResponse
    {
        try {
            $place = $this->placeService->show($place->id);
            return response()->json($place, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StorePlaceRequest $request
     * @return JsonResponse
     */
    public function store(StorePlaceRequest $request): JsonResponse
    {
        try {
            $place = $this->placeService->create($request->validated());
            return response()->json($place, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdatePlaceRequest $request
     * @param Place $place
     * @return JsonResponse
     */
    public function update(UpdatePlaceRequest $request, Place $place): JsonResponse
    {
        try {
            $place = $this->placeService->update($place->id, $request->validated());
            return response()->json($place, Response::HTTP_OK);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Place $place
     * @return JsonResponse
     */
    public function destroy(Place $place): JsonResponse
    {
        try {
            $this->placeService->delete($place->id);
            return response()->json(['message' => 'Place deleted'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Place $place
     * @return JsonResponse
     */
    public function restore(Place $place): JsonResponse
    {
        try {
            $this->placeService->restore($place->id);
            return response()->json(['message' => 'Place restored'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            $payload = $this->placeService->options();

            return response()->json($payload, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(
                ['error' => $e->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param DeletePlaceRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(DeletePlaceRequest $request): JsonResponse
    {
        try {
            $ids=$request->validated("ids");
            $this->placeService->bulkDestroy($ids);
            return response()->json(['message' => 'Les lieux sont supprimés avec succès'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
