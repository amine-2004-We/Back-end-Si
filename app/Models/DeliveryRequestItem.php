<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryRequestItem extends Model
{
    use SoftDeletes;

    protected $table = 'delivery_request_items';

    protected $fillable = [
        'delivery_request_id',
        'article_id',
        'quantity_requested',
    ];

    /**
     * Get the parent delivery request.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_request_id');
    }

    /**
     * Get the specific article requested.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}