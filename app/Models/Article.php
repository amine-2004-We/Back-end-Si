<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Article extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'article_id',
        'name',
        'specifications',
        'brand',
        'reference_price',
        'unit',
        'deleted_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'unit' => 'string',
    ];
    /**
     * Get the product associated with the article.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function purchaseLists()
    {
        return $this->belongsToMany(
            PurchaseList::class,
            'purchase_list_items',
            'article_id',
            'purchase_list_id'
        )->withPivot('quantity');
    }
}
