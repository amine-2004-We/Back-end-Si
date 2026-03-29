<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * class ExpenseNote
 */
class ExpenseNote extends Model
{
    use SoftDeletes;

    protected $table = 'expense_notes';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';

    public const NATURE_TRANSPORT = 'transport';
    public const NATURE_LODGING   = 'lodging';
    public const NATURE_MEALS     = 'meals';
    public const NATURE_MISC      = 'misc';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'beneficiary_type',
        'beneficiary_id',
        'training_id',
        'expense_date',
        'expense_nature',
        'amount_ttc',
        'attachment_path',
        'budget_line_id',
        'validation_status',
        'comments',
        'created_by_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'expense_date' => 'date',
        'amount_ttc'   => 'decimal:2',
    ];

    /**
     * @return MorphTo
     */
    public function beneficiary(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    /**
     * @return BelongsTo
     */
    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class, 'budget_line_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

}
