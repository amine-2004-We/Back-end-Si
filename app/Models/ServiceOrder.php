<?php

namespace App\Models;

use App\Enums\ServiceOrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_order_identifier',
        'purchase_order_id',
        'calltender_id',
        'source_type',
        'subject',
        'start_date',
        'estimated_end_date',
        'supplier_id',
        'supervisor_id',
        'signed_document_path',
        'status',
        'observations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'estimated_end_date' => 'date',
        'status' => ServiceOrderStatusEnum::class,
        'purchase_order_id' => 'integer',
        'calltender_id' => 'integer',
    ];

    /**
     * Get the purchase order that this service order belongs to.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get the calltender that this service order belongs to.
     */
    public function calltender(): BelongsTo
    {
        return $this->belongsTo(Calltender::class, 'calltender_id');
    }

    /**
     * Get the supplier (prestataire) associated with this service order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the user responsible for supervising this service order.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the source (purchase order or calltender) of this service order as a relation.
     * Attention : cette méthode retourne toujours une relation BelongsTo, mais il faut l'utiliser avec ->source pour charger la bonne relation.
     */
    public function source(): BelongsTo
    {
        if ($this->source_type === 'purchase_order') {
            return $this->purchaseOrder();
        } else {
            return $this->calltender();
        }
    }

    /**
     * Scope to filter by purchase order.
     */
    public function scopeByPurchaseOrder($query, $purchaseOrderId)
    {
        return $query->where('purchase_order_id', $purchaseOrderId)
                     ->where('source_type', 'purchase_order');
    }

    /**
     * Scope to filter by calltender.
     */
    public function scopeByCalltender($query, $calltenderId)
    {
        return $query->where('calltender_id', $calltenderId)
                     ->where('source_type', 'calltender');
    }

    /**
     * Scope to filter by source type.
     */
    public function scopeBySourceType($query, $type)
    {
        return $query->where('source_type', $type);
    }

    /**
     * Check if service order has a source.
     */
    public function hasSource(): bool
    {
        return !is_null($this->source_type) && 
               (($this->source_type === 'purchase_order' && $this->purchase_order_id) ||
                ($this->source_type === 'calltender' && $this->calltender_id));
    }
}