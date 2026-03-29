<?php

namespace App\Models;

use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class CategoryController
 */
#[ObservedBy([CategoryObserver::class])]
class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'category_id', 'deleted_at'];

    protected $appends = ['is_deleted'];


    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getIsDeletedAttribute(): bool
    {
        return $this->trashed();
    }
}
