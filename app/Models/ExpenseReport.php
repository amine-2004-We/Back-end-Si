<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ExpenseReport extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'project_id',
        'mission_order_id',
        'created_by_id', // Renommé ici
        'total_amount',
        'status',
        'budget_line_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Statuts possibles
     */
    const STATUS_CREATED = 'created';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VALIDATED_MANAGER = 'validated_manager';
    const STATUS_VALIDATED_TREASURY = 'validated_treasury';
    const STATUS_VALIDATED_ACCOUNTING = 'validated_accounting';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    /**
     * Boot method pour assigner automatiquement le créateur
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by_id) && Auth::check()) {
                $model->created_by_id = Auth::user()->collaborator?->id;
            }
        });
    }

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function missionOrder(): BelongsTo
    {
        return $this->belongsTo(MissionOrder::class);
    }

    /**
     * @return BelongsTo
     */
    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    /**
     * @return BelongsTo
     */
    public function advance(): BelongsTo
    {
        return $this->belongsTo(Advance::class);
    }

    /**
     * @return HasMany
     */
    public function expenseLines(): HasMany
    {
        return $this->hasMany(ExpenseLine::class);
    }

    /**
     * Calcul automatique du montant total
     */
    public function calculateTotalAmount(): float
    {
        $totalLines = $this->expenseLines->sum('amount');
        $advanceAmount = $this->advance ? $this->advance->advance_amount : 0;

        return max(0, $totalLines - $advanceAmount);
    }

    /**
     * Relation vers le collaborateur qui a créé la note
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'created_by_id');
    }

    /**
     * Check if manager amount field should be visible
     * Only after manager validation
     */
    public function getCanShowManagerAmountAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_VALIDATED_MANAGER,
            self::STATUS_VALIDATED_TREASURY,
            self::STATUS_VALIDATED_ACCOUNTING,
            self::STATUS_PAID,
        ]);
    }

    /**
     * Check if finance amount field should be visible
     * Only after manager validation
     */
    public function getCanShowFinanceAmountAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_VALIDATED_MANAGER,
            self::STATUS_VALIDATED_TREASURY,
            self::STATUS_VALIDATED_ACCOUNTING,
            self::STATUS_PAID,
        ]);
    }

    /**
     * Check if finance amount field can be edited
     * Only when status is validated_manager
     */
    public function getCanEditFinanceAmountAttribute(): bool
    {
        return $this->status === self::STATUS_VALIDATED_MANAGER;
    }

    /**
     * Append these attributes to JSON
     */
    protected $appends = [
        'can_show_manager_amount',
        'can_show_finance_amount',
        'can_edit_finance_amount',
    ];
}
