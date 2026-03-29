<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 

class ArticleObserver
{
    /**
     * Handle the Article "creating" event.
     * Generates a unique article_id using a database lock to prevent race conditions.
     */
    public function creating(Article $article): void
    {

        DB::transaction(function () use ($article) {
            $product = Product::find($article->product_id);

            if ($product) {
                $article->article_id = $this->generateNextArticleId($product->product_id);
            } else {
                Log::error("Product with ID {$article->product_id} not found when creating article.");
            }
        });
    }

    /**
     * Handle the Article "updating" event.
     * Updates article_id if the product changes, also with a lock.
     */
    public function updating(Article $article): void
    {
        if ($article->isDirty('product_id')) {
            DB::transaction(function () use ($article) {
                $originalId = $article->getOriginal('article_id');
                $suffix = substr(strrchr($originalId, '-'), 1);

                $newProduct = Product::find($article->product_id);

                if ($newProduct) {
                    $newCustomProductId = $newProduct->product_id;
                    $newArticleId = 'ART-' . $newCustomProductId . '-' . $suffix;

                    if (Article::withTrashed()->where('article_id', $newArticleId)->exists()) {
                        $newArticleId = $this->generateNextArticleId($newProduct->product_id);
                    }
                    Log::info("Updating article_id: $originalId -> $newArticleId");
                    $article->article_id = $newArticleId;
                } else {
                    Log::error("New product with ID {$article->product_id} not found when updating article.");
                }
            });
        }
    }

    /**
     * Handle the Article "restoring" event.
     * Checks for conflicts and generates a new ID if needed.
     */
    public function restoring(Article $article): void
    {
        $existing = Article::where('article_id', $article->article_id)->exists();

        if ($existing) {
            $oldId = $article->article_id;

            $product = Product::find($article->product_id);

            if ($product) {
                // Use the generateNextArticleId to get a guaranteed unique ID.
                $article->article_id = $this->generateNextArticleId($product->product_id);
                Log::warning("Conflict de article_id lors de la restauration ($oldId déjà utilisé). Nouveau: {$article->article_id}");
            } else {
                Log::error("Product with ID {$article->product_id} not found when restoring article.");
            }
        }
    }

    /**
     * Helper function to generate the next unique article ID.
     * The `lockForUpdate()` method is used to acquire a pessimistic lock.
     */
    private function generateNextArticleId(string $productId): string
    {
        $latest = Article::withTrashed()
            ->where('article_id', 'like', 'ART-' . $productId . '-%')
            ->orderByDesc('article_id')
            ->lockForUpdate() // This is the key change: it locks the selected rows.
            ->first();

        $next = 1;
        if ($latest) {
            $lastSuffix = (int) substr(strrchr($latest->article_id, '-'), 1);
            $next = $lastSuffix + 1;
        }
        $suffix = str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        return 'ART-' . $productId . '-' . $suffix;
    }
}
