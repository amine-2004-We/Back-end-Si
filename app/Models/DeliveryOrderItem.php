<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    //
    protected $fillable = [
        'delivery_order_id',
        'article_id',
        'expected_quantity',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
