<?php

namespace App\Models;

use App\Enums\DeliveryRequestStatus;
use App\Enums\PriorityFcEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DeliveryRequestItem;

class DeliveryRequest extends Model
{
    use softDeletes;
    
    protected $table = 'delivery_request';
    
    protected $fillable = [
        'code',
        'purchase_request_id',
        'purchase_order_id',
        'applicant',
        'recipient', 
        'recipient_contact', 
        'request_date',
        'request_purpose',
        'delivery_location',
        'delivery_date',
        'priority',
        'status',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'priority' => PriorityFcEnum::class,
        
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'applicant');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'recipient');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the requested items (articles and quantities) for this request.
     */
    public function items()
    {
        return $this->hasMany(DeliveryRequestItem::class, 'delivery_request_id');
    }
}