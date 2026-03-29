<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class BudgetLine
 */
class BudgetLine extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'budget_lines';

    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'label',
        'budget_category_id',
        'status'
    ];

    /**
     * @return BelongsToMany
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(
            Partner::class,
            'budget_line_partner',
            'budget_line_id',
            'partner_id'
        )
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class,'budget_category_id',);
    }

    /**
     * @return BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->using(BudgetLineProject::class)
            ->withPivot([
                'id',
                'engaged_amount',
                'consumed_amount',
                'remaining_amount',
                'total_amount',
                'unit_amount',
                'quantity',
                'reliquate_amount',
            ]);
    }
}
