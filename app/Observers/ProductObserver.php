<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Category; // We need to use the Category model now
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        $category = Category::find($product->category_id);

        if ($category) {
            $product->product_id = $this->generateNextProductId($category->category_id);
        } else {
            Log::error("Category with ID {$product->category_id} not found when creating product.");
        }
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        if ($product->isDirty('category_id')) {
            $originalId = $product->getOriginal('product_id');
            $suffix = substr(strrchr($originalId, '-'), 1);
            $newCategory = Category::find($product->category_id);

            if ($newCategory) {
                $newMetierCategoryId = $newCategory->category_id;
                $newProductId = 'PROD-' . $newMetierCategoryId . '-' . $suffix;

                Log::info("Mise à jour du product_id: $originalId → $newProductId");

                $product->product_id = $newProductId;
            } else {
                Log::error("New category with ID {$product->category_id} not found when updating product.");
            }
        }
    }

    /**
     * Handle the Product "restoring" event.
     */
    public function restoring(Product $product): void
    {
        $existing = Product::where('product_id', $product->product_id)->exists();

        if ($existing) {
            $oldId = $product->product_id;
            $category = Category::find($product->category_id);
            if ($category) {
                $product->product_id = $this->generateNextProductId($category->category_id);
                Log::warning("Conflit de product_id lors de la restauration ($oldId déjà utilisé). Nouveau: {$product->product_id}");
            } else {
                Log::error("Category with ID {$product->category_id} not found when restoring product.");
            }
        }
    }

    /**
     * Generates the next unique product ID based on the category's metier ID.
     *
     * @param string $metierCategoryId The metier ID for the category.
     * @return string The new product ID.
     */
    private function generateNextProductId(string $metierCategoryId): string
    {
        $latest = Product::withTrashed()
            ->where('product_id', 'like', 'PROD-' . $metierCategoryId . '-%')
            ->orderByDesc('product_id')
            ->first();

        $next = 1;
        if ($latest) {
            $lastSuffix = (int) substr(strrchr($latest->product_id, '-'), 1);
            $next = $lastSuffix + 1;
        }

        $suffix = str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        return 'PROD-' . $metierCategoryId . '-' . $suffix;
    }
}
