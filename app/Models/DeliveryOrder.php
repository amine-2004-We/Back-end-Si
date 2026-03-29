<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    //
    protected $table = 'delivery_orders';
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'purchase_order_id',
        'supplier_id',
        'quote_id',
        'delivery_request_id',
        'delivery_address',
        'expected_delivery_date',
        // 'items',
        'comments',
        'status',
        'created_by'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function quote()
    {
        return $this->belongsTo(Quote::class);       
    }

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    /**
     * Get all delivery receipts associated with this delivery order.
     */
    public function deliveryReceipts(): HasMany
    {
        return $this->hasMany(DeliveryReceipt::class, 'delivery_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
