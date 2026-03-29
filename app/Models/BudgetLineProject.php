<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BudgetLineProject extends Pivot
{
    protected $table = 'budget_line_project';
    public $incrementing = true;   // you have an id() in the migration
    public $timestamps = true;

    protected $fillable = [
        'budget_line_id','project_id','engaged_amount',
        'consumed_amount','quantity','reliquate_amount',
        'total_amount','remaining_amount','unit_amount',
    ];

    public function budgetLinePartners(): HasMany
    {
        return $this->hasMany(BudgetLinePartner::class, 'budget_line_project_id');
    }
}
