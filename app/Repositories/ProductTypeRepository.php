<?php

namespace App\Repositories;

use App\Models\ProductType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProductTypeRepository
{
    /**
     * Get a paginated list of product types with all filters and sorting.
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = ProductType::onlyTrashed();
        } else {
            $query = ProductType::query();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where('name', 'ilike', '%' . $searchTerm . '%');
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        }
         else{
            $query->orderBy('created_at', 'desc'); 
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }

    public function find(int $typeId): ?ProductType
    {
        return ProductType::withTrashed()->find($typeId);
    }

    public function create(array $data): ProductType
    {
        return ProductType::create($data);
    }
    
    /**
     * Update a product type by its ID.
     */
    public function update(int $typeId, array $data): ProductType
    {
        $type = $this->find($typeId);
        if (!$type) {
            throw new ModelNotFoundException("Type de produit non trouvé.");
        }

        if (array_key_exists('deleted_at', $data)) {
            // This logic is for restoring/soft-deleting from the update endpoint
            if ($data['deleted_at'] === false && $type->trashed()) {
                $type->restore();
            } elseif ($data['deleted_at'] === true && !$type->trashed()) {
                if ($type->products()->exists()) {
                    throw new ConflictHttpException('Impossible de supprimer : le type de produit est lié à des produits.');
                }
                $type->delete();
            }
            unset($data['deleted_at']);
        }

        $type->update($data);
        return $type->fresh();
    }

    /**
     * Delete a product type by its ID.
     */
    public function delete(int $typeId): bool
    {
        $type = $this->find($typeId);
        if (!$type) {
            throw new ModelNotFoundException("Type de produit non trouvé.");
        }

        if ($type->products()->exists()) {
            throw new ConflictHttpException("Impossible de supprimer ce type de produit car il est lié à des produits.");
        }

        return $type->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $types = ProductType::with('products')->whereIn('id', $ids)->get();

        foreach ($types as $type) {
            if ($type->products()->exists()) {
                throw new ConflictHttpException("Impossible de supprimer le type de produit ID {$type->id} car il est lié à des produits.");
            }
        }

        return ProductType::whereIn('id', $ids)->delete();
    }

    /**
     * Restore a soft-deleted product type by its ID.
     */
    public function restore(int $id): ProductType
    {
        $type = ProductType::onlyTrashed()->find($id);
        if (!$type) {
            throw new ModelNotFoundException("Type de produit non trouvé ou déjà actif.");
        }
        
        $type->restore();
        return $type;
    }
}