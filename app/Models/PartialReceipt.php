<?php

namespace App\Models;

use App\Enums\PartialReceiptStatusEnum;
use App\Observers\PartialReceiptObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PartialReceiptObserver::class])]
class PartialReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pv_partial_id',
        'received_at',
        'observations',
        'status',
    ];
    /**
     * Les PV provisoires associés (plusieurs, via table pivot).
     */
    public function provisionalAcceptances(): BelongsToMany
    {
        return $this->belongsToMany(ProvisionalAcceptance::class, 'provisional_acceptance_partial_receipt', 'partial_receipt_id', 'provisional_acceptance_id');
    }

    protected $casts = [
        'received_at' => 'date',
        'status' => PartialReceiptStatusEnum::class,
    ];

    /**
     * Les bons de réception associés (plusieurs).
     */
    public function deliveryReceipts(): BelongsToMany
    {
    return $this->belongsToMany(DeliveryReceipt::class, 'delivery_receipt_partial_receipt', 'partial_receipt_id', 'delivery_receipt_id');
    }
}