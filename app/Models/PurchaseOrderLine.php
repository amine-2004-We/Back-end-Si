<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Article;
use App\Models\Product;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'article_id',
        'product_id',
        'quantity',
        'unit_price',
        'tva_rate',
    ];
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    /**
     * Relation vers le produit associé à la ligne de commande
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
