<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurchaseOrder extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'purchase_orders';

    /**
     * @var array
     */
    protected $fillable = [
        'po_number',
        'quote_id',
        'supplier_id',
        'issuer_id',
        'purchase_request_id',
        'subject',
        'issue_date',
        'total_amount_ttc',
        'currency',
        'payment_method',
        'delivery_lead_time_days',
        'terms_file_path',
        'status',
        'validated_by_procurement_manager',
        'validated_by_controlling',
        'validated_by_board',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date',
        'total_amount_ttc' => 'decimal:2',
        'validated_by_procurement_manager' => 'boolean',
        'validated_by_controlling' => 'boolean',
        'validated_by_board' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * @return BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }

    /**
     * @return BelongsTo
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    /***
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    
}
