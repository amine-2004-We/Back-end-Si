<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;


class CategoryRepository
{
    /**
     * @return mixed
     */
    public function getFiltered(array $params)
    {
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Category::onlyTrashed();
        } else {
            $query = Category::query();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findProductsByCategoryId(int $id)
    {
        return Category::with('products')
            ->where('id', $id)
            ->firstOrFail();
    }


    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return Category::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function update(int $id, array $data)
    {
        $category = Category::withTrashed()->where('id', $id)->first();

        if (!$category) {
            throw new ModelNotFoundException('Catégorie non trouvée.');
        }

        if (array_key_exists('deleted_at', $data)) {
            if ($data['deleted_at'] === false) {
                if ($category->trashed()) {
                    $category->restore();
                }
            } elseif ($data['deleted_at'] === true) {
                if ($category->products()->exists()) {
                    throw new ConflictHttpException('Impossible de supprimer : la catégorie est liée à des produits.');
                }

                if (!$category->trashed()) {
                    $category->delete();
                }
            }

            unset($data['deleted_at']);
        }

        $category->update($data);

        return $category;
    }


    /**
     * @param int $id
     * @return Category|null
     */
    public function find(int $id): Category|null
    {
        return Category::find($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $category = $this->find($id);
        if (!$category) {
            throw new ModelNotFoundException("Catégorie non trouvée.");
        }
        return $category->delete();
    }

    /**
     * @param array $ids
     * @return int
     * @throws \Exception
     */
    public function bulkDelete(array $ids): int
    {
        $categories = Category::with('products')->whereIn('id', $ids)->get();

        foreach ($categories as $category) {
            if ($category->products()->exists()) {
                throw new \Exception("Impossible de supprimer la catégorie ID {$category->id} car elle est liée à des produits.", 409);
            }
        }

        return Category::whereIn('id', $ids)->delete();
    }

    /**
     * @param int $id
     * @return Category
     */
    public function restore(int $id): Category
    {
        $category = Category::withTrashed()->where('id', $id)->first();

        if (!$category) {
            throw new ModelNotFoundException("Catégorie non trouvée.");
        }

        if (!$category->trashed()) {
            throw new ConflictHttpException("La catégorie n'est pas supprimée.");
        }

        $category->restore();

        return $category;
    }
}
