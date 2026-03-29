<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialReception extends Model
{
    use SoftDeletes;

    protected $table = 'financial_receptions';

    protected $fillable = [
        'financial_installment_id',
        'reception_date',
        'amount_received',
        'reception_mode',
    ];

    protected $casts = [
        'reception_date' => 'datetime',
        'amount_received' => 'decimal:2',
    ];

    /**
     * A reception belongs to a financial installment.
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(FinancialInstallment::class, 'financial_installment_id');
    }
}
