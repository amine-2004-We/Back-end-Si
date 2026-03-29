<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 *
 */
class ProductRepository
{
    /**
     * @return LengthAwarePaginator
     */
     /**
     * Get a paginated list of products with filtering, sorting, and status handling.
     *
     * @param array $params The query parameters from the HTTP request.
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Product::onlyTrashed();
        } else {
            $query = Product::query();
        }
        $query->with(['category', 'productType']);

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
                    $query->where($key, $value);
                }
            }
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

    /**
     * @param int $id
     * @return Product|Collection|Model|null
     */
    public function find(int $id)
    {

        return Product::withTrashed()->where('id', $id)->first();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return Product::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Product|Collection|Model|null
     */
    public function update(int $id, array $data)
    {
        $product = Product::withTrashed()->where('id', $id)->first();

        if (!$product) {
            throw new ModelNotFoundException("Produit non trouvé.");
        }

        if (array_key_exists('deleted_at', $data)) {
            if ($data['deleted_at'] === false && $product->trashed()) {
                $product->restore();
            } elseif ($data['deleted_at'] === true && !$product->trashed()) {
                if ($product->articles()->exists()) {
                    throw new ConflictHttpException('Impossible de supprimer : le produit est lié à des commandes.');
                }
                $product->delete();
            }
            unset($data['deleted_at']);
        }

        $product->update($data);

        return $product;
    }

    public function delete(int $id)
    {
        $product = $this->find($id);

        if (!$product) {
            throw new ModelNotFoundException("Produit non trouvé.");
        }

        return $product->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Product::whereIn('id', $ids)->delete();
    }

    /**
     * @return LengthAwarePaginator
     */

    /**
 * Restore a soft-deleted product.
 *
 * @param int $id
 * @return Product
 * @throws ModelNotFoundException
 */
public function restore(int $id): Product
{
    $product = Product::onlyTrashed()->where('id', $id)->first();
    if (!$product) {
        throw new ModelNotFoundException("Produit non trouvé ou déjà actif.");
    }
    $product->restore();

    return $product;
}
}
