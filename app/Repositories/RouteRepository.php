<?php

namespace App\Repositories;

use App\Models\RouteModel as Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * class RouteRepository
 */
class RouteRepository
{
    /**
     * @param array $filters
     * @return Builder
     */
    public function withFilters(array $filters): Builder
    {
        $query = Route::query()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderByDesc('created_at');

        if (!empty($filters['route_code'])) {
            $query->where('route_code', 'like', '%' . $filters['route_code'] . '%');
        }
        
        if (!empty($filters['departure_location'])) {
            $query->where('departure_location', 'like', '%' . $filters['departure_location'] . '%');
        }
        
        if (!empty($filters['arrival_location'])) {
            $query->where('arrival_location', 'like', '%' . $filters['arrival_location'] . '%');
        }

        if (!empty($filters['transport_mode'])) {
            $query->where('transport_mode', $filters['transport_mode']);
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'true') {
                $query->withoutTrashed();
            } elseif ($filters['is_active'] === 'false') {
                $query->onlyTrashed();
            }
        } else {
            $query->withoutTrashed();
        }

        return $query;
    }

    /**
     * @param int $id
     * @return Route
     * @throws ModelNotFoundException
     */
    public function find(int $id): Route
    {
        return Route::findOrFail($id);
    }

    /**
     * @param int $id
     * @return Route
     * @throws ModelNotFoundException
     */
    public function findWithTrashed(int $id): Route
    {
        return Route::withTrashed()->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Route
     */
    public function create(array $data): Route
    {
        $route = new Route();
        $route->fill($data);
        $route->save();
        return $route;
    }

    /**
     * @param array $data
     * @param int $id
     * @return Route
     */
    public function update(array $data, int $id): Route
    {
        $route = $this->find($id);
        $route->update($data);
        return $route;
    }

    /**
     * @param int $id
     * @return Route
     */
    public function delete(int $id): Route
    {
        $route = $this->find($id);
        $route->delete();
        return $route;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Route::destroy($ids);
    }

    /**
     * @param int $id
     * @return Route
     * @throws ModelNotFoundException
     */
    public function restore(int $id): Route
    {
        $route = $this->findWithTrashed($id);

        if (!$route) {
            abort(404, 'Route not found.');
        }

        $exists = Route::where('route_code', $route->route_code)
                       ->whereNull('deleted_at')
                       ->exists();

        if ($exists) {
            abort(409, 'A route with the same code already exists.');
        }

        $route->restore();
        return $route;
    }
}
