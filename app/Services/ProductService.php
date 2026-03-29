<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator; 

/**
 *
 */
class ProductService
{
    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFilteredProducts(array $params): LengthAwarePaginator
    {
   
        return $this->productRepository->getFiltered($params);
    }
    /**
     * @param int $id
     * @return Product|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getById(int $id): Product
    {
        return $this->productRepository->find($id);
    }

    /**
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product
    {
        return $this->productRepository->update($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->productRepository->bulkDelete($ids);
    }

    /**
     * @return mixed
     */
  

    public function findWithTrashed(int $id)
    {
        return Product::withTrashed()
            ->where('id', $id)
            ->first();
    }
     /**
     * Restore a soft-deleted product.
     *
     * @param int $id
     * @return Product
     */
    public function restore(int $id): Product
    {
        return $this->productRepository->restore($id);
    }
}
