<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     *
     * @param Category $category
     * @return void
     */
    public function creating(Category $category): void
    {
        $last = Category::withTrashed()->where('category_id', 'like', 'CAT-%')->orderByDesc('category_id')->first();

        $nextNumber = 1;

        if ($last) {
            $lastId = (int) substr(strrchr($last->category_id, '-'), 1);
            $nextNumber = $lastId + 1;
        }
        
        // Pad with zeros to ensure correct numeric ordering (CAT-0001, CAT-0010, CAT-0100)
        $generatedId = 'CAT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // Check if the generated ID already exists, if so, increment until we find an available one
        while (Category::withTrashed()->where('category_id', $generatedId)->exists()) {
            $nextNumber++;
            $generatedId = 'CAT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        $category->category_id = $generatedId;
    }
}
