<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * class BudgetCategory
 */
class BudgetCategory extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'budget_categories';

    /**
     * @var string[]
     */
    protected $fillable = [ 'code', 'label','type','budgetary_area','is_active'];

    /**
     * @return HasMany<BudgetLine>
     */
    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

   public function budgetLineProjects()
    {
        return $this->hasManyThrough(
            BudgetLineProject::class,
            BudgetLine::class,
            'budget_category_id', 
            'budget_line_id',    
            'id',
            'id'
        );
    }
}
