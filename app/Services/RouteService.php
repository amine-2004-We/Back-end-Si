<?php

namespace App\Services;

use App\Models\RouteModel as Route;
use App\Repositories\RouteRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class RouteService
 */
class RouteService
{
    /**
     * @var RouteRepository
     */
    public RouteRepository $routeRepository;

    /**
     * @param RouteRepository $routeRepository
     */
    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        $filters = $request->only([
            'route_code',
            'departure_location',
            'arrival_location',
            'transport_mode',
            'is_active',
            'per_page'
        ]);
        
        $query = $this->routeRepository->withFilters($filters);
        
        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 10;

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Route
     */
    public function show(int $id): Route
    {
        return $this->routeRepository->find($id);
    }

    /**
     * @param array $data
     * @return Route
     */
    public function create(array $data): Route
    {
        return $this->routeRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Route
     */
    public function update(int $id, array $data): Route
    {
        return $this->routeRepository->update($data, $id);
    }

    /**
     * @param int $id
     * @return Route
     */
    public function delete(int $id): Route
    {
        return $this->routeRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDestroy(array $ids): int
    {
        return $this->routeRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return Route
     */
    public function restore(int $id): Route
    {
        try {
            return $this->routeRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
