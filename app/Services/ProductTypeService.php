<?php

namespace App\Services;

use App\Models\ProductType;
use App\Repositories\ProductTypeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductTypeService
{
    protected ProductTypeRepository $repo;

    public function __construct(ProductTypeRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get a paginated and filtered list of product types.
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getFilteredProductTypes(array $params): LengthAwarePaginator
    {
        return $this->repo->getFiltered($params);
    }

    /**
     * Get a single product type by its ID.
     * @param int $id
     * @return ProductType|null
     */
    public function getById(int $id): ?ProductType
    {
        return $this->repo->find($id);
    }

    /**
     * Create a new product type.
     * @param array $data
     * @return ProductType
     */
    public function create(array $data): ProductType
    {
        return $this->repo->create($data);
    }

    /**
     * Update a product type by its ID.
     * @param int $id
     * @param array $data
     * @return ProductType
     */
    public function update(int $id, array $data): ProductType
    {
        return $this->repo->update($id, $data);
    }

    /**
     * Delete a product type by its ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }

    /**
     * Delete multiple product types by their IDs.
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->repo->bulkDelete($ids);
    }
    
    /**
     * Restore a soft-deleted product type by its ID.
     *
     * @param int $id
     * @return ProductType
     */
    public function restore(int $id): ProductType
    {
        return $this->repo->restore($id);
    }
}