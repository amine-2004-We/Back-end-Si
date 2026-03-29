<?php

namespace App\Services;

use App\Models\Pack;
use App\Models\Product;
use App\Repositories\PackRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PackService
{
    public function __construct(protected PackRepository $repository)
    {
    }

    /**
     * Get a paginated and filtered list of packs.
     */
    public function getFilteredPacks(array $params): LengthAwarePaginator
    {
        return $this->repository->getFiltered($params);
    }

    /**
     * Get a single pack by its ID.
     */
    public function getById(int $id): ?Pack
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new pack and sync its productss.
     */
    public function create(array $data): Pack
    {
        if (!empty($data['products'])) {
            $this->checkForDuplicateProducts($data['products']);
            $packData = [
                'name' => $data['name'],
                'description' => (string) $data['description'] ?? null,
                'created_by' => 1,
            ];
            $pack = $this->repository->create($packData);
            $businessProductIds = array_column($data['products'], 'product_id');
            $primaryKeyMap = Product::whereIn('id', $businessProductIds)
                ->pluck('id', 'id')
                ->toArray();

            $productSync = [];

            foreach ($data['products'] as $productData) {
                $businessId = (int) $productData['product_id'];
                if (isset($primaryKeyMap[$businessId])) {
                    $primaryId = $primaryKeyMap[$businessId];
                    $productSync[$primaryId] = ['quantity' => $productData['quantity']];
                }
            }

            $pack->products()->sync($productSync);
        }

        return $pack->load('products');
    }

    /**
     * Update a pack and its associated productss.
     */
    public function update(int $id, array $data): Pack
    {
        DB::beginTransaction();
        try {

            // ✅ ADD THIS LOGIC TO UPDATE THE productsS
            if (array_key_exists('products', $data)) {
                $this->checkForDuplicateProducts($data['products']);
                $pack = $this->repository->update($id, [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                ]);
                if (!empty($data['products'])) {
                    $businessProductIds = array_column($data['products'], 'product_id');
                    $primaryKeyMap = Product::whereIn('id', $businessProductIds)
                        ->pluck('id', 'id')
                        ->toArray();

                    $productsSync = [];

                    foreach ($data['products'] as $productData) {
                        $businessId = (int) $productData['product_id'];
                        if (isset($primaryKeyMap[$businessId])) {
                            $primaryId = $primaryKeyMap[$businessId];
                            $productsSync[$primaryId] = ['quantity' => $productData['quantity']];
                        }
                    }

                    $pack->products()->sync($productsSync);
                } else {
                    // S'il y a un tableau vide, on supprime toutes les liaisons
                    $pack->products()->sync([]);
                }
            }
            DB::commit();
            return $pack->load('products');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted pack.
     */
    public function restore(int $id): Pack
    {
        return $this->repository->restore($id);
    }

    /**
     * Soft delete a pack and detach its products.
     */
    public function delete(int $id): bool
    {
        $pack = $this->repository->find($id);
        if (!$pack) {
            return false;
        }
        //$pack->products()->detach();
        return $this->repository->delete($id);
    }

    /**
     * Bulk delete packs and detach their products.
     */
    public function bulkDelete(array $ids): int
    {
        $packs = Pack::whereIn('id', $ids)->get(); // Use pack_id for consistency
        foreach ($packs as $pack) {
            //$pack->products()->detach();
            $pack->delete();
        }
        return count($packs);
    }

    private function checkForDuplicateProducts(array $products): void
    {
        $productIds = array_column($products, 'product_id');
        $duplicates = array_diff_key($productIds, array_unique($productIds));

        if (!empty($duplicates)) {
            throw new \InvalidArgumentException('Vous avez ajouté plusieurs fois le même produit au pack.');
        }
    }
}
