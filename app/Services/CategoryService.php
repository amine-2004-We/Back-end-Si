<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * class CategoryService
 */
class CategoryService
{
    /**
     * @var CategoryRepository
     */
    protected CategoryRepository $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return LengthAwarePaginator
     */
  public function getFilteredCategories(array $params): LengthAwarePaginator
{
   
    return $this->categoryRepository->getFiltered($params);
}
    /**
     * @param $categoryId
     * @return mixed
     */
    public function getProductsByCategoryId($categoryId): mixed
    {
        return $this->categoryRepository->findProductsByCategoryId($categoryId);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * @param $categoryId
     * @param array $data
     * @return mixed
     */
    public function update($categoryId, array $data): mixed
    {
        return $this->categoryRepository->update($categoryId, $data);
    }

    /**
     * @param $categoryId
     * @return mixed
     */
    public function delete($id): mixed
    {
        $hasRelatedProducts = Product::where('category_id', $id)->exists();
        if ($hasRelatedProducts) {
            throw new ConflictHttpException('Impossible de supprimer : la catégorie est liée à des produits.');
        }
        return $this->categoryRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
  public function bulkDelete(array $ids): int
{

    $hasRelatedProducts = Product::whereIn('id', $ids)->exists();

    if ($hasRelatedProducts) {
        throw new ConflictHttpException('Impossible de supprimer : une ou plusieurs catégories sont liées à des produits.');
    }

    return $this->categoryRepository->bulkDelete($ids);
}

    /**
     * @param $categoryId
     * @return mixed
     */
    public function find($categoryId): mixed
    {
        return $this->categoryRepository->find($categoryId);
    }


   
   

    public function findWithTrashed(int $id): ?Category
    {
        return Category::withTrashed()
            ->where('id', $id)
            ->first();
    }
    /**
     * Restore a soft-deleted category by its ID.
     *
     * @param int $id
     * @return Category
     */
    public function restore(int $id): Category
    {
        $category = Category::withTrashed()->where('id', $id)->first();

        if (!$category) {
            throw new ModelNotFoundException("Catégorie non trouvée.");
        }

        $category->restore();

        return $category;
        }
}
