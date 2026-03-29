<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * class ProductController
 */
class Product extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = ['product_id', 'name', 'description', 'category_id', 'product_type_id', 'deleted_at'];

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }


    /**
     * @return
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'product_id');
    }

    public function packs()
    {
        return $this->belongsToMany(Pack::class, 'product_pack', 'product_id', 'pack_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    
}
