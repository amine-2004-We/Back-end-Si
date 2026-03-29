<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_report_id',
        'designation',
        'type',
        'departure_id',
        'arrival_id',
        'transport_mode',
        'date',
        'label',
        'amount',
        'amount_manager',
        'amount_finance',
        'justification_path'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'amount_manager' => 'decimal:2',
        'amount_finance' => 'decimal:2',
    ];

    /**
     * Types de frais
     */
    const TYPE_RESTAURATION = 'restauration';
    const TYPE_DEPLACEMENT = 'deplacement';
    const TYPE_HEBERGEMENT = 'hebergement';
    const TYPE_TRANSPORT = 'transport';
    const TYPE_AUTRE = 'autre';

    /**
     * @return BelongsTo
     */
    public function expenseReport(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class);
    }

    /**
     * @return BelongsTo
     */
    public function departure(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'departure_id');
    }

    /**
     * @return BelongsTo
     */
    public function arrival(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'arrival_id');
    }

    /**
     * Vérifie si c'est une ligne de déplacement
     */
    public function isTravel(): bool
    {
        return $this->type === self::TYPE_DEPLACEMENT;
    }
}