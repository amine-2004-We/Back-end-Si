<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryReceiptItem extends Model
{
    use SoftDeletes;

    protected $table = 'delivery_receipt_items';

    protected $fillable = [
        'delivery_receipt_id',
        'article_id',
        'quantity_received',
    ];

    /**
     * Get the parent receipt note.
     */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class, 'delivery_receipt_id');
    }

    /**
     * Get the specific article received.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
    
}
