<?php

namespace App\Models;

use App\Enums\DeliveryReceiptStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'delivery_receipts';

    protected $fillable = [
        'delivery_order_id',
        'receipt_identifier',
        'reception_date',
        'receiver_id',
        'storage_location',
        'status',
        'observations',
    ];

    protected $casts = [
        'reception_date' => 'date',
        'status' => DeliveryReceiptStatusEnum::class,
    ];

    /**
     * Get the User (Réceptionnaire) who signed the receipt.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the received items (articles and quantities) for this receipt.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryReceiptItem::class, 'delivery_receipt_id');
    }

    /**
     * Get the partial receipt associated with this delivery receipt.
     */
    public function partialReceipts(): BelongsToMany
    {
        return $this->belongsToMany(
            PartialReceipt::class, 
            'delivery_receipt_partial_receipt', 
            'delivery_receipt_id',              
            'partial_receipt_id'                
        );
    }

    /**
     * Get the DeliveryOrder this receipt belongs to.
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}
