<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ArticleRepository
{
    /**
     * Get a paginated list of articles with filtering, sorting, and status handling.
     *
     * @param array $params The query parameters from the HTTP request.
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {

        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = Article::onlyTrashed();
        } else {
            $query = Article::query();
        }

        $query->with('product');

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {

                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('specifications', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'ilike', '%' . $searchTerm . '%');
            });
        }

        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                if (!empty($value)) {

                    if ($key === 'product_id') {
                        $query->whereHas('product', function ($q) use ($value) {
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
            $query->orderBy($params['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->paginate($perPage);
    }
    /**
     * @param int $id
     * @return Article|Model|Collection|null
     */
    public function find(int $id): Article|Model|Collection|null
    {
        return Article::find($id);
    }

    /**
     * @param array $data
     * @return Article
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
 * @param int $articleId
 * @param array $data
 * @return mixed
 */
public function update(int $articleId, array $data)
{
    $article = Article::withTrashed()->where('id', $articleId)->first();

    if (!$article) {
        throw new ModelNotFoundException("Article non trouvé.");
    }

    if (array_key_exists('deleted_at', $data)) {
        if ($data['deleted_at'] === false && $article->trashed()) {
            $article->restore();
        } elseif ($data['deleted_at'] === true && !$article->trashed()) {
            if ($article->packs()->exists()) {
                throw new ConflictHttpException('Impossible de supprimer : l\'article est lié à un pack.');
            }
            $article->delete();
        }
        unset($data['deleted_at']);
    }

    $article->update($data);

    return $article;
}


    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $article = $this->find($id);

        if (!$article) {
            throw new ModelNotFoundException("Article non trouvé.");
        }
        return $article->delete();
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return Article::whereIn('id', $ids)->delete();
    }

    public function getAllWithTrashed(array $filter = [], int $paginate = 100)
    {
        $query = Article::withTrashed()->with('product');

        if (!empty($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        return $query->paginate($paginate);
    }
    /**
     * Restore a soft-deleted article by its ID.
     *
     * @param int $id
     * @return Article
     */
    public function restore(int $id): Article
    {
        $article = Article::withTrashed()->where('id', $id)->first();

        if (!$article) {
            throw new ModelNotFoundException("Article non trouvé.");
        }

        $article->restore();

        return $article;
    }
}
