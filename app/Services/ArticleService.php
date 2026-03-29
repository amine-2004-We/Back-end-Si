<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Product;
use App\Repositories\ArticleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
    protected ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }
/**
     * Get a paginated and filtered list of articles.
     *
     * @param array $params The query parameters from the controller.
     * @return LengthAwarePaginator
     */
    public function getFilteredArticles(array $params): LengthAwarePaginator
    {
        
        return $this->articleRepository->getFiltered($params);
    }

    public function getById(int $id): Article
    {
        return $this->articleRepository->find($id);
    }

    public function create(array $data): Article
    {
        return $this->articleRepository->create($data);
    }

    /**
     * @param $article_id
     * @param array $data
     * @return Article
     */
    public function update($id, array $data)
    {
        return $this->articleRepository->update($id, $data);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->articleRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return $this->articleRepository->bulkDelete($ids);
    }

    /**
     * @return LengthAwarePaginator
     */
   

    public function findWithTrashed(int $id): Article
    {
        return Article::withTrashed()->with('product')
            ->where('id', $id)
            ->first();
    }
    /**
     * Restore a soft-deleted article by its ID.
     *
     * @param int $id
     * @return Article
     */
    public function restore(int $id): Article
    {
        return $this->articleRepository->restore($id);
    }
}
