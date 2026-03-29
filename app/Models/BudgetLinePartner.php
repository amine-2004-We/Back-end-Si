<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * class BudgetLinePartner
 */
class BudgetLinePartner extends Model
{
    protected $table = 'budget_line_partner';
    /**
     * @var string[]
     */
    protected $fillable = ['partner_id','allocated_amount','budget_line_project_id'];

    /**
     * @return BelongsTo
     */
    public function budgetLineProject(): BelongsTo
    {
        return $this->belongsTo(BudgetLineProject::class, 'budget_line_project_id');
    }

    /**
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
