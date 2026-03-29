<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Invoice
 */
class Invoice extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'receipt_id',
        'invoice_number',
        // --- ADDED ---
        'supplier_invoice_number',
        'reception_date',
        'accounting_account_id',
        'delivery_receipt_id',
        // --- END ADDED ---
        'invoice_date',
        'due_date',
        'total',
        'status',
        'subject',
        'notes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var string[]
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        // --- ADDED ---
        'reception_date' => 'date',
        // --- END ADDED ---
        'total' => 'decimal:2',
    ];
    /**
     * Get the total for the invoice, falling back to purchase order if not set
     */
    public function getTotalAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }
        if ($this->delivery_receipt_id && $this->deliveryReceipt) {
            // Assuming DeliveryReceipt has a relation to DeliveryOrder which has a relation to PurchaseOrder
            // This logic might need adjustment depending on where the total is stored
            return $this->deliveryReceipt->deliveryOrder->purchaseOrder->total_amount_ttc ?? null;
        }
        return null;
    }

    public const STATUS_UNRECEIVED           = 'Non parvenue';
    public const STATUS_PENDING_VALIDATION   = 'En attente de validation';
    public const STATUS_CANCELED             = 'Annulé';
    public const STATUS_PROCESSING           = 'En cours de traitement';
    public const STATUS_PAID                 = 'Payé';
    public const STATUS_REJECTED             = 'Rejeté';
    public const STATUS_ACCOUNTED            = 'Comptabilisé';
    public const STATUS_PROCESSED            = 'Traité';
    public const STATUS_VALIDATED_TREASURY   = 'Validé (Trésorerie)';
    public const STATUS_VALIDATED_ACCOUNTING_1 = 'Validé 1 (Comptabilité)';
    public const STATUS_VALIDATED_CG_2       = 'Validé 2 (CG)';

    /**
     * @var string[]
     */
    public const STATUSES = [
        self::STATUS_UNRECEIVED,
        self::STATUS_PENDING_VALIDATION,
        self::STATUS_CANCELED,
        self::STATUS_PROCESSING,
        self::STATUS_PAID,
        self::STATUS_REJECTED,
        self::STATUS_ACCOUNTED,
        self::STATUS_PROCESSED,
        self::STATUS_VALIDATED_TREASURY,
        self::STATUS_VALIDATED_ACCOUNTING_1,
        self::STATUS_VALIDATED_CG_2,
    ];

    /**
     * Get the accounting account associated with the invoice.
     */
    public function accountingAccount(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyAccount::class, 'accounting_account_id');
    }
    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class, 'delivery_receipt_id');
    }

    /**
     * Relation to Receipt (future implementation)
     */
    // public function receipt()
    // {
    //     return $this->belongsTo(Receipt::class);
    // }
}
