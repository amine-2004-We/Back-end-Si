<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteRouteRequest;
use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\RouteModel as Route;
use App\Services\RouteService;
use App\Enums\TransportMode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class RouteController
 */
class RouteController extends Controller
{
    /** @var RouteService */
    protected RouteService $routeService;

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $routes = $this->routeService->getAll($request);
            return response()->json($routes, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreRouteRequest $request
     * @return JsonResponse
     */
    public function store(StoreRouteRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $route = $this->routeService->create($data);
            return response()->json([
                'message' => 'Trajet créé avec succès',
                'route' => $route,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur dans le serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Route $route
     * @return JsonResponse
     */
    public function show(Route $route): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Trajet récupéré avec succès',
                'route' => $route,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateRouteRequest $request
     * @param Route $route
     * @return JsonResponse
     */
    public function update(UpdateRouteRequest $request, Route $route): JsonResponse
    {
        try {
            $data = $request->validated();
            $updatedRoute = $this->routeService->update($route->id,$data);
            return response()->json([
                'message' => 'Trajet mis à jour avec succès',
                'route' => $updatedRoute,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Route $route
     * @return JsonResponse
     */
    public function destroy(Route $route): JsonResponse
    {
        try {
            $this->routeService->delete($route->id);
            return response()->json(['message' => 'Trajet supprimé avec succès'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteRouteRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DeleteRouteRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $ids = $validated['ids'];
            $count = $this->routeService->bulkDestroy($ids);
            return response()->json(['message' => $count . ' trajet(s) supprimé(s) avec succès !'], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression en masse: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $route = $this->routeService->restore($id);
            return response()->json([
                'message' => 'Trajet restauré avec succès',
                'route' => $route,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la restauration: ' . $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'transport_modes' => TransportMode::options(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
