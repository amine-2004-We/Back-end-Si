<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialInstallment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'convention_id',
        'installment_number',
        'amount',
        'amount_received',
        'reception_mode',
        'is_ttc',
        'due_date',
        'trigger_condition',
        'status',
        'proof_document',
        'devise',
        'reception_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reception_date' => 'datetime',
        'is_ttc' => 'boolean',
        'status' => \App\Enums\InstallmentStatus::class,
        'devise' => \App\Enums\CurrencyEnum::class
    ];


    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class);
    }

    /**
     * A financial installment can have many receptions.
     */
    public function receptions()
    {
        return $this->hasMany(FinancialReception::class, 'financial_installment_id');
    }
}
