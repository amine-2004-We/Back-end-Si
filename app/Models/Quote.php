<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Quote extends Model
{
    use SoftDeletes;

    protected $table = 'quotes';

    protected $fillable = [
        'quote_number',
        'purchase_list_id',
        'supplier_id',
        'quote_date',
        'valid_until',
        'subject',
        'total_amount_ht',
        'vat_amount',
        'total_amount_ttc',
        'estimated_delivery_days',
        'payment_terms',
        'attachment_path',
        'status',
        'remarks',
        'deleted_at',
        'vat_rate'
    ];

    protected $casts = [
        'quote_date'              => 'date',
        'valid_until'             => 'date',
        'total_amount_ht'         => 'decimal:2',
        'vat_amount'              => 'decimal:2',
        'total_amount_ttc'        => 'decimal:2',
        'estimated_delivery_days' => 'integer',
        'payment_terms'           => 'string',
        'status'                  => 'string',
        'attachment_path'         => 'string',
        'remarks'                 => 'string',
        'vat_rate' => 'decimal:4'
    ];

    // --- Status constants ---
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    // --- Payment terms constants ---
    public const TERMS_UPON_RECEIPT  = 'upon_receipt';
    public const TERMS_30D_END_MONTH = '30d_end_month';
    public const TERMS_OTHER         = 'other';

public function purchaseList()
    {
        return $this->belongsTo(PurchaseList::class, 'purchase_list_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function pack()
    {
        return $this->belongsTo(Pack::class, 'pack_id');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    // --- Helpers ---
    /**
     * Recalculer les totaux HT, TVA et TTC.
     *
     * @param float|null $vatRate en % (ex: 20 pour 20%)
     */
    public function recomputeTotals(?float $vatRate = null): void
    {
        $ht = $this->items()->sum(DB::raw('quantity * unit_price_ht'));
        $vat = $vatRate !== null ? round($ht * $vatRate / 100, 2) : (float) $this->vat_amount;
        $ttc = round($ht + $vat, 2);
        $this->total_amount_ht  = $ht;
        $this->vat_amount       = $vat;
        $this->total_amount_ttc = $ttc;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
