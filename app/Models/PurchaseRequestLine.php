<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestLine extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'unit_price',
        'quantity',
        'budget_category_id',
        'budget_line_id',
        'technical_justification',
        'estimated_total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }



public function budgetCategory()
{
    return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
}


    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function purchaseRequest() {
        return $this->belongsTo(PurchaseRequest::class);
    }


    public function pack() {
        return $this->belongsTo(Pack::class);
    }

    public function header() {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
