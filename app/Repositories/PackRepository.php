<?php

namespace App\Repositories;

use App\Models\Pack;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PackRepository
{
    /**
     * Get a paginated list of packs with all filters and sorting.
     *
     * @param array $params The query parameters from the HTTP request.
     * @return LengthAwarePaginator
     */
     public function getFiltered(array $params): LengthAwarePaginator
    {
        if (filter_var($params['withTrashed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query = Pack::onlyTrashed();
        } else {
            $query = Pack::query();
        }

        $query->with(['products', 'creator']);

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'product_id') {
                        $query->whereHas('products', function ($q) use ($value) {
                            $q->where('id', $value);
                        });
                    } else {
                        $query->where($key, $value);
                    }
                }
            }
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';

            if ($params['sort_by'] === 'productsCount') {
                $query->withCount('products')->orderBy('products_count', $direction);
            } else {
                $query->orderBy($params['sort_by'], $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;

        return $query->paginate($perPage);
    }
    /**
     * Find a single pack by its business ID, including trashed ones.
     */
    public function find(int $packId): ?Pack 
    {
        return Pack::withTrashed()->with(['products', 'creator'])->where('id', $packId)->first();
    }

    /**
     * Create a new pack and sync its products.
     */
    public function create(array $data): Pack
    {
        return Pack::create($data);
    }

    /**
     * Update a pack by its business ID.
     */
    public function update(int $packId, array $data): Pack
    {
        $pack = $this->find($packId); 
        if (!$pack) {
            throw new ModelNotFoundException("Pack non trouvé.");
        }

        $pack->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (isset($data['products'])) {
            $pack->products()->sync($data['products']);
        }

        return $pack->fresh(['products', 'creator']);
    }

    public function delete(int $packId): bool
    {
        $pack = $this->find($packId);
        if (!$pack) {
            throw new ModelNotFoundException("Pack non trouvé.");
        }
        return $pack->delete();
    }

    /**
     * Restore a soft-deleted pack by its business ID.
     */
    public function restore(int $packId): Pack
    {
        $pack = Pack::onlyTrashed()->where('id', $packId)->first();
        if (!$pack) {
            throw new ModelNotFoundException("Pack non trouvé ou déjà actif.");
        }

        $pack->restore();
        return $pack;
    }

    /**
     * Bulk delete packs by their business IDs.
     */
    public function bulkDelete(array $packIds): int
    {
        return Pack::whereIn('id', $packIds)->delete();
    }

}
